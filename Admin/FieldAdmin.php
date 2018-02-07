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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Glavweb\CompositeObjectBundle\Entity\Field;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class FieldAdmin
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FieldAdmin extends AbstractAdmin
{
    /**
     * The base route pattern used to generate the routing information
     *
     * @var string
     */
    protected $baseRoutePattern = 'field';

    /**
     * The base route name used to generate the routing information
     *
     * @var string
     */
    protected $baseRouteName = 'field';

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
        $collection->clearExcept(['edit', 'create']);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Field $field */
//        $field = $this->getSubject();
//        $container = $this->getConfigurationPool()->getContainer();

        $formMapper
                ->add('name')
                ->add('label')
                ->add('type', null, [
                    'choice_translation_domain' => $this->getTranslationDomain()
                ])
                ->add('linkedClass')
                ->add('required')
                ->add('denormalized')
                ->add('isList')
                ->add('isFilter')
                ->add('plainPosition', HiddenType::class)
//            ->add('options')
        ;
    }

    /**
     * @param mixed $field
     */
    public function prePersist($field)
    {
        /** @var Field $field */
    }

    /**
     * @param mixed $field
     */
    public function preUpdate($field)
    {
        /** @var Field $field */
    }
}
