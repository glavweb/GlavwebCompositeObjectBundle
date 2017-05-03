<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Twig;

use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Manager\ObjectManager;
use Glavweb\CompositeObjectBundle\Provider\Field\FieldProviderRegistry;

/**
 * Class CmsCompositeObjectExtension
 *
 * @package Glavweb\CompositeObjectBundle\Twig
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class CmsCompositeObjectExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var FieldProviderRegistry
     */
    private $fieldProviderRegistry;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * CmsCompositeObjectExtension constructor.
     *
     * @param FieldProviderRegistry  $fieldProviderRegistry
     * @param ObjectManager $objectManager
     */
    public function __construct(FieldProviderRegistry $fieldProviderRegistry, ObjectManager $objectManager)
    {
        $this->fieldProviderRegistry = $fieldProviderRegistry;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return [
            'objectManager' => $this->objectManager
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('co_get_value_data', [$this, 'getValueData'])
        ];
    }

    /**
     * @param AbstractValue $value
     * @return mixed
     * @throws \Exception
     */
    public function getValueData(AbstractValue $value)
    {
        $field     = $value->getField();
        $fieldType = $field->getType();
        $fieldProvider = $this->fieldProviderRegistry->get($fieldType);

        return $fieldProvider->getValueData($value);
    }
}