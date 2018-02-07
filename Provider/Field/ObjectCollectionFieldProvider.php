<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Provider\Field;

use Glavweb\CompositeObjectBundle\Entity\Field;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueObjectCollection;
use Glavweb\CompositeObjectBundle\Form\ObjectCollectionType;
use Glavweb\CompositeObjectBundle\Service\ApiDataManager;
use Glavweb\CompositeObjectBundle\Service\FixtureCreator;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;

/**
 * Class ObjectCollectionFieldProvider
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ObjectCollectionFieldProvider extends AbstractFieldProvider
{
    /**
     * @var string
     */
    protected $type = 'object_collection';

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var FieldProviderRegistry
     */
    private $fieldProviderRegistry;

    /**
     * ObjectFieldProvider constructor.
     *
     * @param FormFactory $formFactory
     * @param Registry $doctrine
     * @param FieldProviderRegistry $fieldProviderRegistry
     */
    public function __construct(FormFactory $formFactory, Registry $doctrine, FieldProviderRegistry $fieldProviderRegistry)
    {
        parent::__construct($formFactory);

        $this->doctrine              = $doctrine;
        $this->fieldProviderRegistry = $fieldProviderRegistry;
    }

    /**
     * @param Field $field
     * @param ObjectInstance $objectInstance
     * @param null $data
     * @return AbstractValue
     * @throws \Exception
     */
    public function createValue(Field $field, ObjectInstance $objectInstance, $data = null)
    {
        $value = new ValueObjectCollection();
        $this->populateValue($value, $field, $objectInstance, $data);

        return $value;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $valueData
     * @param ObjectManipulator $objectManipulator
     * @throws \Exception
     */
    public function updateValue(AbstractValue $value, $valueData, ObjectManipulator $objectManipulator)
    {
        if (!$value instanceof ValueObjectCollection) {
            throw new \Exception('Value must be instance of ValueObjectCollection.');
        }

        $em          = $this->doctrine->getManager();
        $restObjects = $value->getObjects();

        foreach ($valueData as $key => $item) {
            if (isset($restObjects[$key])) {
                $object = $restObjects[$key];
                unset($restObjects[$key]);

            } else {
                $linkedClass = $value->getField()->getLinkedClass();
                if (!$linkedClass) {
                    throw new \Exception('The linked class must be defined for ObjectField entity.');
                }

                $object = new ObjectInstance();
                $object->setClass($linkedClass);
                $object->setValueObjectCollection($value);
                $object->setParent($value->getInstance());

                $em->persist($object);
            }

            $objectManipulator->saveObject($object, $item);
        }

        foreach ($restObjects as $restObject) {
            $em->remove($restObject);
        }
    }

    /**
     * @param mixed          $data
     * @param Field          $field
     * @param FixtureCreator $fixtureCreator
     * @return ObjectInstance[]
     */
    public function createValueDataByFixture($data, Field $field, FixtureCreator $fixtureCreator)
    {
        $linkedClass = $field->getLinkedClass();

        $objects = [];
        foreach ($data as $item) {
            $objects[] = $fixtureCreator->createInstance($linkedClass, $item, null);
        }

        return $objects;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $data
     * @throws \Exception
     */
    public function setValueData(AbstractValue $value, $data)
    {
        if (!$value instanceof ValueObjectCollection) {
            throw new \Exception('Value must be instance of ValueObjectCollection.');
        }

        foreach ($data as $object) {
            $value->addObject($object);
        }
    }

    /**
     * @param AbstractValue $value
     * @return \Doctrine\Common\Collections\Collection|mixed
     * @throws \Exception
     */
    public function getValueData(AbstractValue $value = null)
    {
        if ($value === null) {
            return [];
        }

        if (!$value instanceof ValueObjectCollection) {
            throw new \Exception('Value must be instance of ValueObjectCollection.');
        }

        return $value->getObjects();
    }

    /**
     * @param AbstractValue $value
     * @return mixed
     * @throws \Exception
     */
    public function getFormData(AbstractValue $value = null)
    {
        if ($value === null) {
            return [];
        }

        $fieldProviderRegistry = $this->fieldProviderRegistry;
        $field = $value->getField();

        if (!$value instanceof ValueObjectCollection) {
            throw new \Exception('Value must be instance of ValueObjectCollection.');
        }

        if (!$field->getLinkedClass()) {
            throw new \Exception('Linked class is not defined.');
        }

        /** @var Field[] $linkedFields */
        $linkedFields = $field->getLinkedClass()->getFields();

        $data = [];
        if ($value) {
            foreach ($value->getObjects() as $object) {
                $linkedData = [];

                foreach ($linkedFields as $linkedField) {
                    $linkedValue = $this->getValueByInstanceField($object, $linkedField);

                    if (!$linkedValue) {
                        continue;
                    }

                    $linkedFieldProvider = $fieldProviderRegistry->get($linkedField->getType());
                    $linkedData[$linkedField->getName()] = $linkedFieldProvider->getFormData($linkedValue);
                }

                $data[] = $linkedData;
            }
        }

        return $data;
    }

    /**
     * @param AbstractValue $value
     * @param ApiDataManager $apiDataManager
     * @return array|mixed []
     * @throws \Exception
     */
    public function getApiData(AbstractValue $value, ApiDataManager $apiDataManager)
    {
        $fieldProviderRegistry = $this->fieldProviderRegistry;
        $field = $value->getField();

        if (!$value instanceof ValueObjectCollection) {
            throw new \Exception('Value must be instance of ValueObjectCollection.');
        }

        if (!$field->getLinkedClass()) {
            throw new \Exception('Linked class is not defined.');
        }

        /** @var Field[] $linkedFields */
        $linkedFields = $field->getLinkedClass()->getFields();

        $data = [];
        if ($value) {
            foreach ($value->getObjects() as $object) {
                $linkedData = [];

                $linkedData['id'] = $object->getId();
                foreach ($linkedFields as $linkedField) {
                    $linkedValue = $this->getValueByInstanceField($object, $linkedField);

                    if (!$linkedValue) {
                        continue;
                    }

                    $linkedFieldProvider = $fieldProviderRegistry->get($linkedField->getType());
                    $linkedData[$linkedField->getName()] = $linkedFieldProvider->getApiData($linkedValue, $apiDataManager);
                }

                $data[] = $linkedData;
            }
        }

        return $data;
    }

    /**
     * @param Field         $field
     * @param AbstractValue $value
     * @param array         $options
     * @return FormBuilderInterface
     */
    public function createFormBuilder(Field $field, AbstractValue $value = null, $options = [])
    {
        $name             = $field->getName();
        $required         = $field->getRequired();
        $type             = $this->getFormType();
        $data             = $this->getFormData($value);
        $fieldsDefinition = $this->getFormFieldsDefinition($field);

        $formBuilder = $this->formFactory->createNamedBuilder($name, $type, $data, array_merge([
            'entry_type'    => ObjectCollectionType::class,
            'entry_options' => ['fields' => $fieldsDefinition],
            'label'         => false,
            'required'      => $required,
            'mapped'        => false,
            'allow_add'     => true,
            'allow_delete'  => true,
        ], $options));

        return $formBuilder;
    }

    /**
     * @param Field $field
     * @return array
     */
    private function getFormFieldsDefinition(Field $field)
    {
        /** @var Field[] $linkedFields */
        $fieldProviderRegistry = $this->fieldProviderRegistry;
        $linkedFields          = $field->getLinkedClass()->getFields();

        $fieldsDefinition = [];
        foreach ($linkedFields as $linkedField) {
            $linkedFieldProvider = $fieldProviderRegistry->get($linkedField->getType());
            $formOptions         = $linkedFieldProvider->getFormOptions($field);
            $label = $linkedField->getLabel() ?: $linkedField->getName();

            $fieldsDefinition[] = [
                'name'    => $linkedField->getName(),
                'type'    => $linkedFieldProvider->getFormType(),
                'options' => array_merge([
                    'label' => $label,
                    'allow_extra_fields' => true
                ], $formOptions),
            ];
        }

        return $fieldsDefinition;
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return CollectionType::class;
    }

    /**
     * @param Field $field
     * @return string
     */
    public function getFormTab(Field $field)
    {
        $label = $field->getLabel() ?: $field->getName();

        return $label;
    }

    /**
     * @param ObjectInstance $instance
     * @param Field $field
     * @return AbstractValue
     */
    private function getValueByInstanceField(ObjectInstance $instance, Field $field)
    {
        $em = $this->doctrine->getManager();

        return $em->getRepository(AbstractValue::class)->findOneBy([
            'instance' => $instance->getId(),
            'field'    => $field->getId()
        ]);
    }
}