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
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueLink;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueObject;
use Glavweb\CompositeObjectBundle\Provider\Field\FieldProviderRegistry;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use Glavweb\CompositeObjectBundle\Repository\ObjectInstanceRepository;
use MongoDB\BSON\ObjectID;
use MongoDB\Collection;
use Glavweb\MongoDBBundle\Registry as MongoDBRegistry;

/**
 * Class ObjectManipulator
 *
 * @package Glavweb\CompositeObjectBundle\Service
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ObjectManipulator
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var MongoDBRegistry
     */
    private $mongodb;

    /**
     * @var FieldProviderRegistry
     */
    private $fieldProviderRegistry;

    /**
     * @var ApiDataManager
     */
    private $apiDataManager;

    /**
     * ObjectManipulator constructor.
     *
     * @param Registry $doctrine
     * @param MongoDBRegistry $mongodb
     * @param FieldProviderRegistry $fieldProviderRegistry
     * @param ApiDataManager $apiDataManager
     */
    public function __construct(Registry $doctrine, MongoDBRegistry $mongodb, FieldProviderRegistry $fieldProviderRegistry, ApiDataManager $apiDataManager)
    {
        $this->doctrine              = $doctrine;
        $this->mongodb               = $mongodb;
        $this->fieldProviderRegistry = $fieldProviderRegistry;
        $this->apiDataManager        = $apiDataManager;
    }

    /**
     * @param ObjectInstance $objectInstance
     * @param array $data
     * @throws \Exception
     */
    public function saveObject(ObjectInstance $objectInstance, array $data)
    {
        $em = $this->doctrine->getManager();

        $objectClass = $objectInstance->getClass();

        foreach ($data as $valueName => $valueData) {
            $field = $this->getFieldByClassAndName($objectClass, $valueName);
            $value = $this->getValueByInstanceAndField($objectInstance, $field);

            if (!$value) {
                $value = $this->createValue($objectInstance, $field);
                $em->persist($value);
            }

            $this->updateValue($value, $valueData);
        }

        $em->flush();

        $this->saveInMongoDB($objectInstance);
        $this->updateLinkedValuesInMongoDB($objectInstance);
    }

    /**
     * @param $objectInstance
     */
    private function updateLinkedValuesInMongoDB($objectInstance)
    {
        $em = $this->doctrine->getManager();

        /** @var EntityRepository $valueLinkRepository */
        $valueLinkRepository = $em->getRepository(ValueLink::class);

        // Update in mongoDB linked values
        $linkedValues = $valueLinkRepository->findBy([
            'link' => $objectInstance
        ]);

        foreach ($linkedValues as $linkedValue) {
            /** @var ValueLink $linkedValue */
            $linkedObject = $linkedValue->getInstance();
            $this->saveInMongoDB($linkedObject);

            $this->updateLinkedValuesInMongoDB($linkedObject);
        }
    }

    /**
     * @param ObjectClass $class
     */
    public function updatePositionsInMongoDBByClass(ObjectClass $class): void
    {
        $em = $this->doctrine->getManager();

        /** @var ObjectInstanceRepository $objectInstanceRepository */
        $objectInstanceRepository = $em->getRepository(ObjectInstance::class);
        $objectInstances = $objectInstanceRepository->findBy(['class' => $class]);

        foreach ($objectInstances as $objectInstance) {
            $this->updatePositionInMongoDB($objectInstance);
        }
    }

    /**
     * @param ObjectInstance $objectInstance
     */
    public function deleteObject(ObjectInstance $objectInstance)
    {
        /** @var EntityRepository $valueLinkRepository */
        $em = $this->doctrine->getManager();
        $valueLinkRepository   = $em->getRepository(ValueLink::class);
        $valueObjectRepository = $em->getRepository(ValueObject::class);
        $objectRepository    = $em->getRepository(ObjectInstance::class);

        // Remove linked values
        $linkedValues = $valueLinkRepository->findBy([
            'link' => $objectInstance
        ]);

        foreach ($linkedValues as $linkedValue) {
            /** @var ValueLink $linkedValue */
            $linkedObject = $linkedValue->getInstance();

            if ($linkedValue->getField()->getRequired()) {
                $this->deleteObject($linkedObject);

            } else {
                $linkedValue->setLink(null);
                $this->saveInMongoDB($linkedObject);
            }
        }

        // Remove object values
        $objectValues = $valueObjectRepository->findBy([
            'object' => $objectInstance
        ]);

        foreach ($objectValues as $objectValue) {
            /** @var ValueObject $objectValue */
            $this->deleteObject($objectValue->getInstance());
        }

        // Remove object collections
        $objectCollections = $objectRepository->findBy([
            'valueObjectCollection' => $objectInstance
        ]);

        foreach ($objectCollections as $objectCollection) {
            $this->deleteObject($objectCollection);
        }

        $em = $this->doctrine->getManager();
        $em->remove($objectInstance);

        $this->deleteFromMongoDB($objectInstance);

        $em->flush();
    }

    /**
     * @param ObjectInstance $instance
     * @param Field $field
     * @param mixed $data
     * @return AbstractValue
     * @throws \Exception
     */
    public function createValue(ObjectInstance $instance, Field $field, $data = null)
    {
        $fieldProvider = $this->fieldProviderRegistry->get($field->getType());
        $value = $fieldProvider->createValue($field, $instance, $data);

        return $value;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $data
     * @throws \Exception
     */
    private function updateValue(AbstractValue $value, $data)
    {
        $fieldProvider = $this->fieldProviderRegistry->get($value->getField()->getType());
        $fieldProvider->updateValue($value, $data, $this);
    }

    /**
     * @param ObjectClass $class
     * @param $name
     * @return Field
     * @throws \Exception
     */
    private function getFieldByClassAndName(ObjectClass $class, $name)
    {
        /** @var EntityRepository $fieldRepository */
        $em = $this->doctrine->getManager();
        $fieldRepository = $em->getRepository(Field::class);

        /** @var Field $field */
        $field = $fieldRepository->findOneBy([
            'class' => $class->getId(),
            'name'  => $name
        ]);

        if (!$field) {
            throw new \Exception('Field is not found.');
        }

        return $field;
    }

    /**
     * @param ObjectInstance $objectInstance
     * @param Field $field
     * @return AbstractValue
     * @throws \Exception
     */
    private function getValueByInstanceAndField(ObjectInstance $objectInstance, Field $field)
    {
        /** @var EntityRepository $valueRepository */
        $em = $this->doctrine->getManager();
        $valueRepository = $em->getRepository(AbstractValue::class);

        /** @var AbstractValue $value */
        $value = $valueRepository->findOneBy([
            'instance' => $objectInstance,
            'field'    => $field
        ]);

        return $value;
    }

    /**
     * Insert or update document in MongoDB
     *
     * @param ObjectInstance $objectInstance
     */
    public function saveInMongoDB(ObjectInstance $objectInstance)
    {
        $mongodbId = $objectInstance->getMongodbId();
        $data      = $this->apiDataManager->getObjectData($objectInstance);

        if ($mongodbId) {
            $this->doUpdateInMongoDB($objectInstance, $mongodbId, $data);

        } else {
            $this->doInsertIntoMongoDB($objectInstance, $data);
        }
    }

    /**
     * @param ObjectInstance $objectInstance
     */
    public function deleteFromMongoDB(ObjectInstance $objectInstance)
    {
        /** @var Collection $collection */
        $className  = $objectInstance->getClass()->getName();
        $database   = $this->mongodb->getDatabase();
        $collection = $database->$className;

        $mongodbId = $objectInstance->getMongodbId();
        $deleteResult = $collection->deleteOne(
            ['_id' => new ObjectID($mongodbId)]
        );

        if (!$deleteResult->isAcknowledged()) {
            throw new \RuntimeException(sprintf('Error when delete to MongoDB (instance: %s).',
                $objectInstance->getId()
            ));
        }
    }

    /**
     * @param ObjectInstance $object
     * @param array $data
     */
    private function doInsertIntoMongoDB(ObjectInstance $object, array $data): void
    {
        /** @var Collection $collection */
        $className  = $object->getClass()->getName();
        $database   = $this->mongodb->getDatabase();
        $collection = $database->$className;

        // set position
        $data['_position'] = $object->getPosition();

        $insertOneResult = $collection->insertOne($data);
        $insertedId = $insertOneResult->getInsertedId();

        if (!$insertOneResult->isAcknowledged() || !$insertedId instanceof ObjectID) {
            throw new \RuntimeException(sprintf('Error when insert to MongoDB (instance: %s).',
                $object->getId()
            ));
        }

        // Update mongoDB ID
        $object->setMongodbId((string)$insertedId);
        $this->doctrine->getManager()->flush();
    }

    /**
     * @param ObjectInstance $object
     * @param string $mongodbId
     * @param array $data
     */
    private function doUpdateInMongoDB(ObjectInstance $object, string $mongodbId, array $data): void
    {
        /** @var Collection $collection */
        $className  = $object->getClass()->getName();
        $database   = $this->mongodb->getDatabase();
        $collection = $database->$className;

        // set position
        $data['_position'] = $object->getPosition();

        $updateResult = $collection->replaceOne(
            ['_id' => new ObjectID($mongodbId)],
            $data,
            ['upsert' => true]
        );

        if (!$updateResult->isAcknowledged()) {
            throw new \RuntimeException(sprintf('Error when update to MongoDB (instance: %s).',
                $object->getId()
            ));
        }
    }

    /**
     * @param ObjectInstance $object
     */
    private function updatePositionInMongoDB(ObjectInstance $object): void
    {
        /** @var Collection $collection */
        $className  = $object->getClass()->getName();
        $database   = $this->mongodb->getDatabase();
        $collection = $database->$className;

        $updateResult = $collection->updateOne(
            ['_id' => new ObjectID($object->getMongodbId())],
            [
                '$set' => ['_position' => $object->getPosition()]
            ],
            ['upsert' => true]
        );

        if (!$updateResult->isAcknowledged()) {
            throw new \RuntimeException(sprintf('Error when update to MongoDB (instance: %s).',
                $object->getId()
            ));
        }
    }
}