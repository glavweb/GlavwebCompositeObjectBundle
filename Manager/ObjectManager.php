<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Manager;

use Glavweb\CompositeObjectBundle\Entity\Field;
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Provider\Field\FieldProviderRegistry;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class ObjectManager
 *
 * @package Glavweb\CompositeObjectBundle\Manager
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ObjectManager
{
    /**
     * @var ObjectClass[]
     */
    private static $objectClassesCache;

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var FieldProviderRegistry
     */
    private $fieldProviderRegistry;

    /**
     * @var string
     */
    private $defaultGroupName;

    /**
     * ObjectManager constructor.
     *
     * @param Registry $doctrine
     * @param FieldProviderRegistry $fieldProviderRegistry
     * @param string $defaultGroupName
     */
    public function __construct(Registry $doctrine, FieldProviderRegistry $fieldProviderRegistry, $defaultGroupName = 'Objects')
    {
        $this->doctrine              = $doctrine;
        $this->fieldProviderRegistry = $fieldProviderRegistry;
        $this->defaultGroupName      = $defaultGroupName;
    }

    /**
     * @return ObjectClass[]
     */
    public function getObjectClasses()
    {
        if (self::$objectClassesCache === null) {
            $doctrine              = $this->doctrine;
            $objectClassRepository = $doctrine->getRepository(ObjectClass::class);

            $objectClasses = $objectClassRepository->findBy([
                'isSubclass' => false
            ]);

            self::$objectClassesCache = $objectClasses;
        }

        return self::$objectClassesCache;
    }

    /**
     * @return ObjectClass[]
     */
    public function getGroupedObjectClasses()
    {
        $objectClasses = $this->getObjectClasses();
        $grouped = [];

        foreach ($objectClasses as $objectClass) {
            $group = $objectClass->getGroup() ?: $this->defaultGroupName;

            $grouped[$group][] = $objectClass;
        }

        return $grouped;
    }

    /**
     * @param Field $field
     * @param ObjectInstance $instance
     * @return AbstractValue|null
     */
    public function getValue(Field $field, ObjectInstance $instance)
    {
        $valueRepository = $this->doctrine->getRepository(AbstractValue::class);

        return $valueRepository->getValueByFieldAndInstance($field, $instance);
    }

    /**
     * @param Field $field
     * @param ObjectInstance $objectInstance
     * @return mixed
     */
    public function getValueData(Field $field, ObjectInstance $objectInstance)
    {
        $value = $this->getValue($field, $objectInstance);

        if ($value instanceof AbstractValue) {
            $fieldProvider = $this->fieldProviderRegistry->get($field->getType());

            return $fieldProvider->getValueData($value);
        }

        return null;
    }
}