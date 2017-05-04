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

use Glavweb\CompositeObjectBundle\Entity\Field;
use Glavweb\CompositeObjectBundle\Entity\NotificationRecipient;
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Provider\Field\FieldProviderRegistry;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Glavweb\MongoDBBundle\Registry as MongoDBRegistry;

/**
 * Class FixtureCreator
 *
 * @package Glavweb\CompositeObjectBundle\Service
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FixtureCreator
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var \Glavweb\MongoDBBundle\Registry
     */
    private $mongodb;

    /**
     * @var ObjectManipulator
     */
    private $objectManipulator;

    /**
     * @var FieldProviderRegistry
     */
    private $fieldProviderRegistry;

    /**
     * @var array
     */
    private $fixture;

    /**
     * @var array
     */
    private $objectClasses = [];

    /**
     * FixtureCreator constructor.
     * @param Registry $doctrine
     * @param MongoDBRegistry $mongodb
     * @param ObjectManipulator $objectManipulator
     * @param FieldProviderRegistry $fieldProviderRegistry
     */
    public function __construct(Registry $doctrine, MongoDBRegistry $mongodb, ObjectManipulator $objectManipulator, FieldProviderRegistry $fieldProviderRegistry)
    {
        $this->doctrine              = $doctrine;
        $this->mongodb               = $mongodb;
        $this->objectManipulator     = $objectManipulator;
        $this->fieldProviderRegistry = $fieldProviderRegistry;
    }

    /**
     * @param array $fixture
     * @param bool  $force
     */
    public function create(array $fixture, $force = false)
    {
        $this->fixture = $fixture;

        $em = $this->doctrine->getManager();
        $em->transactional(function($em) use ($fixture, $force) {
            if ($force) {
                $this->clearDatabase();
            }

            foreach ($fixture as $fixtureItem) {
                if (!isset($fixtureItem['class'])) {
                    throw new \Exception('Class must be defined.');
                }

                // Create class and fields
                $class = $this->createClass($fixtureItem['class']);

                // Remove all documents in mongodb collection
//                $this->cleanMongodbCollection($class);

                if (isset($fixtureItem['instances'])) {
                    $this->createInstances($class, $fixtureItem['instances']);
                }
            }
        });
    }

    /**
     * @param array $classData
     * @return ObjectClass
     * @throws \Exception
     */
    public function createClass(array $classData)
    {
        if (!isset($classData['name'])) {
            throw new \Exception('Name must be defined.');
        }

        $em = $this->doctrine->getManager();

        $class = new ObjectClass();
        $this->objectClasses[$classData['name']] = $class;

        $class->setName($classData['name']);

        if (isset($classData['group'])) {
            $class->setGroup($classData['group']);
        }

        if (isset($classData['label'])) {
            $class->setLabel($classData['label']);
        }

        if (isset($classData['to_string_template'])) {
            $class->setToStringTemplate($classData['to_string_template']);
        }

        if (isset($classData['is_subclass'])) {
            $class->setIsSubclass($classData['is_subclass']);
        }

        if (isset($classData['notification']['enabled'])) {
            $class->setNotificationEnabled((bool)$classData['notification']['enabled']);
        }

        if (isset($classData['captcha']['enabled'])) {
            $class->setCaptchaEnabled((bool)$classData['captcha']['enabled']);
        }

        if (isset($classData['captcha']['options'])) {
            $class->setCaptchaOptions((array)$classData['captcha']['options']);
        }

        if (isset($classData['api_methods'])) {
            $class->setApiMethods($classData['api_methods']);
        }

        $em->persist($class);

        $recipients = isset($classData['notification']['recipients']) ? $classData['notification']['recipients'] : [];
        foreach ($recipients as $recipientEmail) {
            $recipient = new NotificationRecipient();
            $recipient->setClass($class);
            $recipient->setEmail($recipientEmail);

            $em->persist($recipient);
        }

        $em->flush();

        $fields = $classData['fields'];
        foreach ($fields as $field) {
            $field = $this->createField($class, $field);

            $em->persist($field);
            $em->flush();
        }

        return $class;
    }

    /**
     * @param ObjectClass $class
     * @param array $fieldData
     * @return Field
     * @throws \Exception
     */
    public function createField(ObjectClass $class, array $fieldData)
    {
        $type         = $fieldData['type'];
        $name         = $fieldData['name'];
        $link         = isset($fieldData['link']) ? $fieldData['link'] : null;
        $label        = isset($fieldData['label']) ? $fieldData['label'] : null;
        $required     = isset($fieldData['required']) ? $fieldData['required'] : null;
        $denormalized = isset($fieldData['denormalized']) ? $fieldData['denormalized'] : null;
        $isList       = isset($fieldData['list']) ? $fieldData['list'] : null;
        $isFilter     = isset($fieldData['filter']) ? $fieldData['filter'] : null;
        $options      = isset($fieldData['options']) ? $fieldData['options'] : [];

        $fieldProvider = $this->fieldProviderRegistry->get($type);

        $linkedClass = $link ? $this->getObjectClassByLink($link) : null;
        $field = $fieldProvider->createField($name, $class, $linkedClass, $label, $required, $denormalized, $isList, $isFilter, $options);

        return $field;
    }

    /**
     * @param ObjectClass $class
     * @param array $instances
     */
    public function createInstances(ObjectClass $class, array $instances)
    {
        foreach ($instances as $fixtureId => $instanceData) {
            $this->createInstance($class, $instanceData, $fixtureId);
        }
    }

    /**
     * @param ObjectClass $class
     * @param array $instanceData
     * @param string|null $fixtureId
     * @return ObjectInstance
     */
    public function createInstance(ObjectClass $class, array $instanceData, $fixtureId = null)
    {
        $em = $this->doctrine->getManager();

        $instance = new ObjectInstance();
        $instance->setFixtureId($fixtureId);
        $instance->setClass($class);

        foreach ($instanceData as $fieldName => $valueData) {
            $field = $this->getFieldByClassAndName($class, $fieldName);
            $value = $this->createValue($instance, $field, $valueData);

            $em->persist($value);
        }

        $em->persist($instance);
        $em->flush();

        $this->objectManipulator->saveInMongoDB($instance);

        return $instance;
    }

    /**
     * @param ObjectInstance $instance
     * @param Field $field
     * @param $data
     * @return AbstractValue
     * @throws \Exception
     */
    public function createValue(ObjectInstance $instance, Field $field, $data)
    {
        $data  = $this->createValueData($field, $data);
        $value = $this->objectManipulator->createValue($instance, $field, $data);

        return $value;
    }

    /**
     * @param Field  $field
     * @param mixed  $data
     * @return array|ObjectInstance|mixed
     * @throws \Exception
     */
    public function createValueData(Field $field, $data)
    {
        $type = $field->getType();

        $fieldProvider = $this->fieldProviderRegistry->get($type);
        $data = $fieldProvider->createValueDataByFixture($data, $field, $this);

        return $data;
    }

    /**
     * @param ObjectClass $class
     * @param $name
     * @return Field
     */
    public function getFieldByClassAndName(ObjectClass $class, $name)
    {
        /** @var EntityRepository $fieldRepository */
        $em = $this->doctrine->getManager();
        $fieldRepository = $em->getRepository(Field::class);

        /** @var Field $field */
        $field = $fieldRepository->findOneBy([
            'class' => $class->getId(),
            'name'  => $name
        ]);

        return $field;
    }

    /**
     * @param string $linkName
     * @return ObjectClass
     * @throws \Exception
     */
    private function getObjectClassByLink($linkName)
    {
        if (!isset($this->objectClasses[$linkName])) {
            throw new \Exception('Object class not found for link "' . $linkName . '".');
        }

        return $this->objectClasses[$linkName];
    }

    /**
     * @return mixed
     */
    private function clearDatabase()
    {
        $em = $this->doctrine->getManager();

        /** @var EntityRepository $objectClassRepository */
        $objectClassRepository = $em->getRepository('GlavwebCompositeObjectBundle:ObjectClass');
        $qb = $objectClassRepository->createQueryBuilder('t')
            ->delete()
        ;

        // Remove images?

        // Drop MongoDB
        $this->mongodb->getDatabase()->drop();

        return $qb->getQuery()->execute();
    }

    /**
     * @param ObjectClass $class
     */
    private function cleanMongodbCollection(ObjectClass $class)
    {
        $className = $class->getName();

        $database = $this->mongodb->getDatabase();
        $collection = $database->$className;
        $collection->deleteMany([]);
    }
}