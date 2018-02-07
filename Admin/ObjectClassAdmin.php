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

use Glavweb\CmsCoreBundle\Admin\AbstractAdmin;
use Glavweb\CmsCoreBundle\Admin\HasSortable;
use Glavweb\CompositeObjectBundle\Entity\Field;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class ObjectClassAdmin
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ObjectClassAdmin extends AbstractAdmin implements HasSortable
{
    /**
     * The base route pattern used to generate the routing information
     *
     * @var string
     */
    protected $baseRoutePattern = 'object-class';

    /**
     * The base route name used to generate the routing information
     *
     * @var string
     */
    protected $baseRouteName = 'object_class';

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->formOptions['translation_domain'] = $this->getTranslationDomain();
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('label')
            ->add('group')
            ->add('isSubclass')
            ->add('notificationEnabled')
            ->add('captchaEnabled')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('label')
            ->add('group')
            ->add('isSubclass')
            ->add('notificationEnabled')
            ->add('captchaEnabled')
            ->add('apiMethods', 'html', [
                'template' => '@GlavwebCompositeObject/admin/object_class/list_field_api_methods.html.twig'
            ])
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
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var ObjectClass $objectClass */
//        $objectClass = $this->getSubject();
//        $container = $this->getConfigurationPool()->getContainer();
        $formMapper
            ->tab('tab.general')
                ->with('group.general', ['class' => 'col-md-6 header-hidden', 'label' => 'Common'])->end()
                ->with('group.additional', ['class' => 'col-md-6 header-hidden', 'label' => 'Additional'])->end()
            ->end()
            ->tab('tab.fields')
                ->with('group.fields', ['class' => 'col-md-12 header-hidden', 'label' => 'Fields'])->end()
            ->end()
        ;

        $formMapper
            ->tab('tab.general')
                ->with('group.general')
                    ->add('name')
                    ->add('label')
                    ->add('toStringTemplate')
                    ->add('group')
                ->end()
                ->with('group.additional')
                    ->add('apiMethods', ChoiceType::class, [
                        'choices' => [
                            'list' => 'list',
                            'view' => 'view'
                        ],
                        'multiple' => true
                    ])
//                    ->add('captchaOptions') // Temporary commented
                    ->add('isSubclass')
                    ->add('notificationEnabled')
                    ->add('captchaEnabled')
                ->end()
            ->end()

            ->tab('tab.fields')
                ->with('group.fields')
                    ->add('fields', 'sonata_type_collection',
                        [
                            'label'        => false,
                            'required'     => true,
                            'by_reference' => false,
                            'type_options' => [
                                'delete'   => true,
                                'required' => true,
                            ],
                            'translation_domain' => 'filed'
                        ],
                        [
                            'edit'         => 'inline',
                            'inline'       => 'table',
                            'allow_delete' => true,
                            'sortable'     => 'plainPosition'
                        ]
                    )
                ->end()
            ->end()

//            ->add('linkedFields', 'sonata_type_collection',
//                array(
//                    'required'     => false,
//                    'type_options' => array(
//                        'delete'   => true,
//                        'required' => true,
//                    ),
//                ),
//                array(
//                    'edit'         => 'inline',
//                    'inline'       => 'table',
//                    'allow_delete' => true,
//                )
//            )
        ;
    }

    /**
     * @param mixed $objectClass
     */
    public function prePersist($objectClass)
    {
        /** @var ObjectClass $objectClass */
    }

    /**
     * @param mixed $objectClass
     */
    public function preUpdate($objectClass)
    {
        /** @var ObjectClass $objectClass */
        foreach ($objectClass->getFields() as $field) {
            /** @var Field $field */
            $field->setPosition((int)$field->getPlainPosition() - 1);
        }
    }
}
