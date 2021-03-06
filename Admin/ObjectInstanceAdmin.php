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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Glavweb\CompositeObjectBundle\CompositeObjectEvents;
use Glavweb\CompositeObjectBundle\Entity\Field;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueBoolean;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueInteger;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueString;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueText;
use Doctrine\ORM\QueryBuilder;
use Glavweb\CompositeObjectBundle\Event\PostPersistEvent;
use Glavweb\CompositeObjectBundle\Event\PostRemoveEvent;
use Glavweb\CompositeObjectBundle\Event\PostUpdateEvent;
use Glavweb\CompositeObjectBundle\Event\PrePersistEvent;
use Glavweb\CompositeObjectBundle\Event\PreRemoveEvent;
use Glavweb\CompositeObjectBundle\Event\PreUpdateEvent;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\FormInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

/**
 * Class ObjectInstanceAdmin
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ObjectInstanceAdmin extends AbstractObjectInstanceAdmin
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
     * @var array
     */
    private $instanceData;

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->add('update_mongodb', 'update-mongodb');
    }

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

        if ($this->getObjectClass()->getNotificationEnabled()) {
            $router = $this->getConfigurationPool()->getContainer()->get('router');
            $menu->addChild('recipients', [
                'uri' => $router->generate('notification_recipient_list', ['class' => $this->getObjectClassName()]),
                'label' => $this->trans('tab.label_recipients')
            ]);
        }

        $menu->addChild('update_mongodb', [
            'uri' => $this->generateUrl('update_mongodb', ['class' => $this->getObjectClassName()]),
            'label' => $this->trans('tab.label_update_objects_in_mongodb')
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

        /** @var Field[] $fields */
        $fields = $objectInstance->getClass()->getFields();
        foreach ($fields as $field) {
            $value = $this->getValueByInstanceField($objectInstance, $field);

            $this->buildFormElement($formMapper, $field, $value);
        }

        $colSize = count($this->getFormGroups()) > 1 ? 8 : 12;

        $formMapper
            ->tab('default', ['label' => 'tab.label_default'])
                ->with('default', ['class' => 'col-md-' . $colSize . ' header-hidden', 'label' => ''])->end()
            ->end()
        ;
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
                    ]), null, [], [
                        'help' => $field->getHelp()
                    ])
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
     * @param AbstractValue $value
     * @return mixed
     */
    public function toStringValueData(AbstractValue $value)
    {
        $valueData = $this->getValueData($value);

        if ($valueData instanceof Collection) {
            $valueData = $valueData->toArray();
        }

        if (is_array($valueData)) {
            return implode(', ', array_map(function ($item) {
                return (string)$item;
            }, $valueData));
        }

        return (string)$valueData;
    }

    /**
     * @param mixed $entity
     */
    public function prePersist($entity)
    {
        $this->preUpdateEntity($entity, true);
    }

    /**
     * @param mixed $entity
     */
    public function preUpdate($entity)
    {
        $this->preUpdateEntity($entity, false);
    }

    /**
     * @param mixed $entity
     */
    public function postPersist($entity)
    {
        if (!$entity instanceof ObjectInstance) {
            throw new \RuntimeException('The $object must be ObjectInstance.');
        }

        $event = new PostPersistEvent($entity, $this->instanceData); // $instanceData define in preUpdateEntity()
        $this->getEventDispatcher()->dispatch(CompositeObjectEvents::POST_PERSIST, $event);

    }

    /**
     * @param mixed $entity
     */
    public function postUpdate($entity)
    {
        if (!$entity instanceof ObjectInstance) {
            throw new \RuntimeException('The $object must be ObjectInstance.');
        }

        $event = new PostUpdateEvent($entity, $this->instanceData); // $instanceData define in preUpdateEntity()
        $this->getEventDispatcher()->dispatch(CompositeObjectEvents::POST_UPDATE, $event);

    }

    /**
     * {@inheritdoc}
     */
    public function preRemove($entity)
    {
        if (!$entity instanceof ObjectInstance) {
            throw new \RuntimeException('The $object must be ObjectInstance.');
        }

        $objectManipulator = $this->getContainer()->get('glavweb_cms_composite_object.object_manipulator');
        $objectManipulator->deleteObject($entity);

        $event = new PreRemoveEvent($entity);
        $this->getEventDispatcher()->dispatch(CompositeObjectEvents::PRE_REMOVE, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove($entity)
    {
        if (!$entity instanceof ObjectInstance) {
            throw new \RuntimeException('The $object must be ObjectInstance.');
        }

        $event = new PostRemoveEvent($entity);
        $this->getEventDispatcher()->dispatch(CompositeObjectEvents::POST_REMOVE, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements)
    {
        if ($actionName === 'delete') {
            /** @var EntityManager $em */
            $em                       = $this->getDoctrine()->getManager();
            $objectInstanceRepository = $em->getRepository(ObjectInstance::class);
            $objectManipulator        = $this->getContainer()->get('glavweb_cms_composite_object.object_manipulator');

            $objects = $objectInstanceRepository->findBy([
                'id' => $idx
            ]);

            foreach ($objects as $object) {
                $objectManipulator->deleteObject($object);
            }

            // Hidden because sonata admin bundle throw exception if $idx is empty.
            // Waiting approving https://github.com/sonata-project/SonataAdminBundle/pull/4659
            // $idx = []; // avoiding duplicate deleting
        }
    }

    /**
     * @param ObjectInstance $instance
     * @param bool $persist
     */
    protected function preUpdateEntity($instance, $persist)
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

        $this->instanceData = $data;

        $dispatcher = $this->getEventDispatcher();

        if ($persist) {
            $event = new PrePersistEvent($instance, $data);
            $dispatcher->dispatch(CompositeObjectEvents::PRE_PERSIST, $event);

        } else {
            $event = new PreUpdateEvent($instance, $data);
            $dispatcher->dispatch(CompositeObjectEvents::PRE_UPDATE, $event);
        }

        return ;
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

    /**
     * @return object|\Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher|\Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    protected function getEventDispatcher()
    {
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        return $dispatcher;
    }
}
