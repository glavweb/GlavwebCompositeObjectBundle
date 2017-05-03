<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Service;

use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Provider\Field\FieldProviderRegistry;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class ApiDataManager
 *
 * @package Glavweb\CompositeObjectBundle\Service
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ApiDataManager
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var FieldProviderRegistry
     */
    private $fieldProviderRegistry;

    /**
     * ObjectApiData constructor.
     * @param Registry $doctrine
     * @param FieldProviderRegistry $fieldProviderRegistry
     */
    public function __construct(Registry $doctrine, FieldProviderRegistry $fieldProviderRegistry)
    {
        $this->doctrine              = $doctrine;
        $this->fieldProviderRegistry = $fieldProviderRegistry;
    }

    /**
     * @param ObjectInstance $object
     * @return array
     * @throws \Exception
     */
    public function getObjectData(ObjectInstance $object)
    {
        $em              = $this->doctrine->getManager();
        $valueRepository = $em->getRepository(AbstractValue::class);

        $values = $valueRepository->findBy([
            'instance' => $object
        ], ['id' => 'ASC']);

        $data = [];
        foreach ($values as $value) {
            $field     = $value->getField();
            $fieldName = $field->getName();
            $fieldType = $field->getType();
            $fieldProvider = $this->fieldProviderRegistry->get($fieldType);

            $data['id']       = $value->getInstance()->getId();
            $data[$fieldName] = $fieldProvider->getApiData($value, $this);
        }

        return $data;
    }
}