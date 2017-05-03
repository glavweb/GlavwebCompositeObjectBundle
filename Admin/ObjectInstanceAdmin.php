<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Admin;

use Glavweb\CompositeObjectBundle\Entity\Field;
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueBoolean;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueInteger;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueString;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueText;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

/**
 * Class ObjectInstanceAdmin
 *
 * @package Glavweb\CompositeObjectBundle\Admin
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ObjectInstanceAdmin extends AbstractAdmin
{
    /**
     * The base route pattern used to generate the routing information
     *
     * @var string
     */
    protected $baseRoutePattern = 'composite-object-instance';

    /**
     * The base route name used to generate the routing information
     *
     * @var string
     */
    protected $baseRouteName = 'composite_object_instance';

    /**
     * @param MenuItemInterface $menu
     * @param $action
     * @param AdminInterface $childAdmin
     * @return mixed|void
     */
    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if ($childAdmin !== null || !in_array($action, ['list'])) {
            return;
        }

        // @todo Только если возможны получатели
        $router = $this->getConfigurationPool()->getContainer()->get('router');
        $menu->addChild('Получатели', [
            'uri' => $router->generate('notification_recipient_list', array('class' => $this->getObjectClassName()))
        ]);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        /** @var Field[] $fields */
        $fields = $this->getObjectClass()->getFields();

        foreach ($fields as $field) {
            if (!$field->getIsFilter()) {
                continue;
            }

            $fieldName = $field->getName();
            $fieldId   = $field->getId();
            $label     = $field->getLabel() ?: $fieldName;

            $callbackFilterMethod = 'callbackFilter' . ucfirst($field->getType());
            if (!method_exists($this, $callbackFilterMethod)) {
                continue;
            }

            $fieldType = $field->getType();
            $filterFieldTypes = [
                'text'    => 'text',
                'string'  => 'text',
                'integer' => 'integer',
                'boolean' => 'checkbox',
            ];
            $filterFieldType = isset($filterFieldTypes[$fieldType]) ? $filterFieldTypes[$fieldType] : 'text';

            $datagridMapper->add('field_' . $fieldId, 'doctrine_orm_callback',
                [
                    'label'      => $label,
                    'callback'   => [$this, $callbackFilterMethod],
                    'field_type' => $filterFieldType
                ]
            );
        }
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        /** @var Field[] $fields */
        $fields = $this->getObjectClass()->getFields();

        $limit = 10;
        $i     = 0;
        foreach ($fields as $field) {
            $isAllowedListField = $field->getIsList();
            if (!$isAllowedListField) {
                continue;
            }

            $i++;
            if ($i > $limit) {
                break;
            }

            $fieldName = $field->getName();
            $label = $field->getLabel() ?: $fieldName;
            $listMapper
                ->add('value_' . $fieldName, null, [
                    'label'    => $label,
                    'template' => 'GlavwebCompositeObjectBundle:admin:list_field_value.html.twig',
                    'field'    => $field
                ])
            ;
        }

        $listMapper
            ->add('_sort', 'actions', array(
                'template' => 'GlavwebCmsCoreBundle:CRUD:list__sort.html.twig',
            ))
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ]
            ])
        ;
    }

    /**
     * @param FormMapper $formMapper
     * @throws \Exception
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var ObjectInstance $objectInstance */
        $objectInstance = $this->getSubject();

        if (!$objectInstance->getId()) {
            $class = $this->getObjectClass();
            $objectInstance->setClass($class);
        }

        $formMapper
            ->tab('Common')
                ->with('Common', ['class' => 'col-md-8', 'name' => 'Общее'])->end()
            ->end()
        ;

        /** @var Field[] $fields */
        $fields = $objectInstance->getClass()->getFields();
        foreach ($fields as $field) {
            $value = $this->getValueByInstanceField($objectInstance, $field);

            $this->buildFormElement($formMapper, $field, $value);
        }
    }

    /**
     * @param FormMapper $formMapper
     * @param Field $field
     * @param AbstractValue|null $value
     */
    private function buildFormElement(FormMapper $formMapper, Field $field, AbstractValue $value = null)
    {
        $fieldProvider = $this->getFieldProvider($field);

        $formMapper
            ->tab($fieldProvider->getFormTab($field))
                ->with($fieldProvider->getFormGroup($field), $fieldProvider->getFormGroupOptions($field))
                    ->add($fieldProvider->createFormBuilder($field, $value, [
                        'mapped' => false
                    ]))
                ->end()
            ->end()
        ;
    }

    /**
     * @param ObjectInstance $instance
     * @param Field $field
     * @return AbstractValue
     */
    public function getValueByInstanceField(ObjectInstance $instance, Field $field)
    {
        $em = $this->getDoctrine()->getManager();

        return $em->getRepository(AbstractValue::class)->findOneBy([
            'instance' => $instance->getId(),
            'field'    => $field->getId()
        ]);
    }

    /**
     * @param AbstractValue $value
     * @return mixed
     */
    public function getValueData(AbstractValue $value)
    {
        $fieldProvider = $this->getFieldProvider($value->getField());

        return $fieldProvider->getValueData($value);
    }

    /**
     * @param ObjectInstance $instance
     */
    protected function updateEntity($instance)
    {
        if (!$this->getRequest()->isMethod('POST')) {
            return;
        }

        $instance->setUpdatedAt(new \DateTime());

        /** @var FormInterface[] $elements */
        $data = [];
        $elements = $this->getForm()->all();
        foreach ($elements as $element) {
            $fieldName = $element->getName();
            $valueData = $element->getData();

            $data[$fieldName] = $valueData;
        }

        $objectManipulator = $this->getContainer()->get('glavweb_cms_composite_object.object_manipulator');
        $objectManipulator->saveObject($instance, $data);

        return ;
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove($object)
    {
        $objectManipulator = $this->getContainer()->get('glavweb_cms_composite_object.object_manipulator');
        $objectManipulator->deleteObject($object);
    }

    /**
     * @param Field $field
     * @return \Glavweb\CompositeObjectBundle\Provider\Field\FieldProviderInterface
     * @throws \Exception
     */
    private function getFieldProvider(Field $field)
    {
        $fieldProviderRegistry = $this->getContainer()->get('glavweb_cms_composite_object.field_provider_registry');
        $fieldProvider = $fieldProviderRegistry->get($field->getType());

        return $fieldProvider;
    }

    /**
     * @param ProxyQuery $proxyQuery
     * @param string $alias
     * @param string $field
     * @param string $value
     * @return void
     */
    public function callbackFilterString(ProxyQuery $proxyQuery, $alias, $field, $value)
    {
        $this->abstractCallbackFilter(
            $proxyQuery,
            $alias,
            $field,
            $value,
            ValueString::class,
            'string',
            true
        );
    }

    /**
     * @param ProxyQuery $proxyQuery
     * @param string $alias
     * @param string $field
     * @param string $value
     * @return void
     */
    public function callbackFilterText(ProxyQuery $proxyQuery, $alias, $field, $value)
    {
        $this->abstractCallbackFilter(
            $proxyQuery,
            $alias,
            $field,
            $value,
            ValueText::class,
            'text',
            true
        );
    }

    /**
     * @param ProxyQuery $proxyQuery
     * @param string $alias
     * @param string $field
     * @param string $value
     * @return void
     */
    public function callbackFilterInteger(ProxyQuery $proxyQuery, $alias, $field, $value)
    {
        $this->abstractCallbackFilter(
            $proxyQuery,
            $alias,
            $field,
            $value,
            ValueInteger::class,
            'integer',
            true,
            true
        );
    }

    /**
     * @param ProxyQuery $proxyQuery
     * @param string $alias
     * @param string $field
     * @param string $value
     * @return void
     */
    public function callbackFilterBoolean(ProxyQuery $proxyQuery, $alias, $field, $value)
    {
        $this->abstractCallbackFilter(
            $proxyQuery,
            $alias,
            $field,
            $value,
            ValueBoolean::class,
            'boolean'
        );
    }

    /**
     * @param ProxyQuery $proxyQuery
     * @param string $alias
     * @param string $field
     * @param string $value
     * @param string $valueClass
     * @param string $valueFieldName
     * @param bool   $isLike
     * @return void
     */
    public function abstractCallbackFilter(ProxyQuery $proxyQuery, $alias, $field, $value, $valueClass, $valueFieldName, $isLike = false, $cast = false)
    {
        if (empty($value['value'])) {
            return;
        }

        $fieldId   = substr($field, 6);
        $valueData = $isLike ? '%' . mb_strtolower($value['value'], 'UTF-8') . '%' : $value['value'];
        $uniqid    = uniqid();
        $valueOperator     = $isLike ? 'LIKE' : '=';
        $valueFieldNameDQL = $cast ? "CAST(s_$uniqid.$valueFieldName AS TEXT)" : "s_$uniqid.$valueFieldName";
        $valueFieldNameDQL = $isLike ? "LOWER($valueFieldNameDQL)" : $valueFieldNameDQL;

        /** @var QueryBuilder $qb */
        $qb = $proxyQuery->getQueryBuilder();
        $qb
            ->join("$alias.values", "v_$uniqid")
            ->andWhere("v_$uniqid.id IN (SELECT s_$uniqid.id FROM $valueClass as s_$uniqid WHERE s_$uniqid.field = :field_$uniqid AND $valueFieldNameDQL $valueOperator :value_$uniqid)")
            ->setParameter('field_' . $uniqid, $fieldId)
            ->setParameter('value_' . $uniqid, $valueData)
        ;
    }
}
