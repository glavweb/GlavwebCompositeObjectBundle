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
use Glavweb\CompositeObjectBundle\Entity\Value\ValueObject;
use Glavweb\CompositeObjectBundle\Service\ApiDataManager;
use Glavweb\CompositeObjectBundle\Service\FixtureCreator;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Sonata\CoreBundle\Form\Type\ImmutableArrayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;

/**
 * Class ObjectFieldProvider
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ObjectFieldProvider extends AbstractFieldProvider
{
    /**
     * @var string
     */
    protected $type = 'object';

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
     * @return ValueObject
     * @throws \Exception
     */
    public function createValue(Field $field, ObjectInstance $objectInstance, $data = null)
    {
        $value = new ValueObject();
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
        if (!$value instanceof ValueObject) {
            throw new \Exception('Value must be instance of ValueObject.');
        }

        $object = $value->getObject();

        if (!$object) {
            $linkedClass = $value->getField()->getLinkedClass();
            if (!$linkedClass) {
                throw new \Exception('The linked class must be defined for ObjectField entity.');
            }

            $object = new ObjectInstance();
            $object->setClass($linkedClass);
            $object->setParent($value->getInstance());

            $em = $this->doctrine->getManager();
            $em->persist($object);

            $value->setObject($object);
        }

        $objectManipulator->saveObject($object, $valueData);
    }

    /**
     * @param mixed          $data
     * @param Field          $field
     * @param FixtureCreator $fixtureCreator
     * @return ObjectInstance
     */
    public function createValueDataByFixture($data, Field $field, FixtureCreator $fixtureCreator)
    {
        $object = $fixtureCreator->createInstance($field->getLinkedClass(), $data, null);

        return $object;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $data
     * @throws \Exception
     */
    public function setValueData(AbstractValue $value, $data)
    {
        if (!$value instanceof ValueObject) {
            throw new \Exception('Value must be instance of ValueObject.');
        }

        $value->setObject($data);
    }

    /**
     * @param AbstractValue $value
     * @return ObjectInstance|mixed
     * @throws \Exception
     */
    public function getValueData(AbstractValue $value = null)
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof ValueObject) {
            throw new \Exception('Value must be instance of ValueObject.');
        }

        return $value->getObject();
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

        $field = $value->getField();

        if (!$value instanceof ValueObject) {
            throw new \Exception('Value must be instance of ValueObject.');
        }

        if (!$field->getLinkedClass()) {
            throw new \Exception('Linked class is not defined.');
        }

        $fieldProviderRegistry = $this->fieldProviderRegistry;

        $data = [];
        foreach ($value->getObject()->getValues() as $value) {
            /** @var AbstractValue $value */
            $field = $value->getField();
            $fieldProvider = $fieldProviderRegistry->get($field->getType());

            $data[$value->getField()->getName()] = $fieldProvider->getValueData($value);
        }

        return $data;
    }

    /**
     * @param Field         $field
     * @param AbstractValue $value
     * @param array         $options
     * @return FormBuilderInterface
     * @throws \Exception
     */
    public function createFormBuilder(Field $field, AbstractValue $value = null, $options = [])
    {
        if (!$field->getLinkedClass()) {
            throw new \Exception('Linked class is not defined.');
        }

        $name     = $field->getName();
        $required = $field->getRequired();
        $type     = $this->getFormType();
        $options  = array_merge($this->getFormOptions($field), $options);

        $formBuilder = $this->formFactory->createNamedBuilder($name, $type, $this->getFormData($value), array_merge([
            'label'    => false,
            'required' => $required,
            'mapped'   => false,
        ], $options));

        return $formBuilder;
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getFormOptions(Field $field)
    {
        $keys = $this->getKeysForObjectFormElement($field);

        return [
            'keys' => $keys
        ];
    }

    /**
     * @param Field $field
     * @return array
     * @throws \Exception
     */
    private function getKeysForObjectFormElement(Field $field)
    {
        /** @var Field[] $linkedFields */
        $fieldProviderRegistry = $this->fieldProviderRegistry;
        $linkedFields          = $field->getLinkedClass()->getFields();

        $keys = [];
        foreach ($linkedFields as $linkedField) {
            $linkedFieldProvider = $fieldProviderRegistry->get($linkedField->getType());

            $name     = $linkedField->getName();
            $type     = $linkedFieldProvider->getFormType();
            $required = $linkedField->getRequired();
            $label    = $linkedField->getLabel() ?: $linkedField->getName();

            $keys[] = [$name, $type, ['label' => $label, 'required' => $required]];
        }

        return $keys;
    }

    /**
     * @param AbstractValue $value
     * @param ApiDataManager $apiDataManager
     * @return array
     * @throws \Exception
     */
    public function getApiData(AbstractValue $value, ApiDataManager $apiDataManager)
    {
        $fieldProviderRegistry = $this->fieldProviderRegistry;
        $field = $value->getField();

        if (!$value instanceof ValueObject) {
            throw new \Exception('Value must be instance of ValueObject.');
        }

        if (!$field->getLinkedClass()) {
            throw new \Exception('Linked class is not defined.');
        }

        /** @var Field[] $linkedFields */
        $linkedFields = $field->getLinkedClass()->getFields();

        $data = [];

        $data['id'] = $value->getObject()->getId();
        foreach ($linkedFields as $linkedField) {
            $linkedFieldProvider = $fieldProviderRegistry->get($linkedField->getType());

            $linkedData = null;
            if ($value) {
                $linkedValue = $this->getValueByInstanceField($value->getObject(), $linkedField);
                $linkedData  = $linkedFieldProvider->getValueData($linkedValue);
            }

            $data[$linkedField->getName()] = $linkedData;
        }

        return $data;
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

    /**
     * @return string
     */
    public function getFormType()
    {
        return ImmutableArrayType::class;
    }

    /**
     * @param Field $field
     * @return string
     */
    public function getFormTab(Field $field)
    {
        return 'default';
    }

    /**
     * @param Field $field
     * @return string
     */
    public function getFormGroup(Field $field)
    {
        $fieldName = $field->getLabel();

        return $fieldName;
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getFormGroupOptions(Field $field)
    {
        $label = $field->getLabel() ?: $field->getName();

        return ['class' => 'col-md-4', 'label' => $label];
    }
}