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
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Service\FixtureCreator;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class AbstractFieldProvider
 *
 * @package Glavweb\CompositeObjectBundle\Provider\Field
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class AbstractFieldProvider implements FieldProviderInterface
{
    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * AbstractFieldProvider constructor.
     *
     * @param FormFactory $formFactory
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $data
     */
    abstract public function setValueData(AbstractValue $value, $data);

    /**
     * @param Field $field
     * @param ObjectInstance $objectInstance
     * @param mixed|null $data
     * @return mixed
     */
    abstract function createValue(Field $field, ObjectInstance $objectInstance, $data = null);

    /**
     * @param AbstractValue $value
     * @param mixed $valueData
     * @param ObjectManipulator $objectManipulator
     */
    abstract public function updateValue(AbstractValue $value, $valueData, ObjectManipulator $objectManipulator);

    /**
     * @param mixed $data
     * @param Field $field
     * @param FixtureCreator $fixtureCreator
     * @return mixed
     */
    abstract public function createValueDataByFixture($data, Field $field, FixtureCreator $fixtureCreator);

    /**
     * @param AbstractValue $value
     * @return mixed
     */
    abstract public function getValueData(AbstractValue $value = null);

    /**
     * @return mixed
     */
    abstract public function getFormType();

    /**
     * @return string
     * @throws \Exception
     */
    public function getType()
    {
        if (!$this->type) {
            throw new \Exception('Field type is not defined.');
        }

        return $this->type;
    }

    /**
     * @param string      $name
     * @param ObjectClass $class
     * @param ObjectClass $linkedClass
     * @param string      $label
     * @param bool        $required
     * @param bool        $denormalized
     * @param bool        $isList
     * @param bool        $isFilter
     * @param array       $options
     * @return Field
     */
    public function createField($name, ObjectClass $class, ObjectClass $linkedClass = null, string $label = null, bool $required = null, bool $denormalized = null, bool $isList = null, bool $isFilter = null, array $options = [])
    {
        $field = new Field();

        $field->setName($name);
        $field->setType($this->getType());
        $field->setClass($class);

        if ($linkedClass) {
            $field->setLinkedClass($linkedClass);
        }

        if ($label !== null) {
            $field->setLabel($label);
        }

        if ($required !== null) {
            $field->setRequired($required);
        }

        if ($denormalized !== null) {
            $field->setDenormalized($denormalized);
        }

        if ($isList !== null) {
            $field->setIsList($isList);
        }

        if ($isFilter !== null) {
            $field->setIsFilter($isFilter);
        }

        $field->setOptions($options);

        return $field;
    }

    /**
     * @param AbstractValue $value
     * @param Field $field
     * @param ObjectInstance $objectInstance
     * @param mixed|null $data
     * @throws \Exception
     */
    protected function populateValue(AbstractValue $value, Field $field, ObjectInstance $objectInstance, $data = null)
    {
        // Common fields
        $value->setField($field);
        $value->setInstance($objectInstance);

        if ($data) {
            $this->setValueData($value, $data);
        }
    }

    /**
     * @param Field $field
     * @param AbstractValue $value
     * @param array $options
     * @return FormBuilderInterface
     */
    public function createFormBuilder(Field $field, AbstractValue $value = null, $options = [])
    {
        if (!isset($options['constraints'])) {
            $options['constraints'] = [];
        }

        $options['constraints'] = array_merge($options['constraints'], $this->getValidationConstraints($field));

        $formName     = $field->getName();
        $formLabel    = $field->getLabel() ?: $formName;
        $formRequired = $field->getRequired();
        $formType     = $this->getFormType();
        $formData     = $this->getFormData($value);
        $formOptions  = array_merge($this->getFormOptions($field), $options);

        $formBuilder = $this->formFactory->createNamedBuilder($formName, $formType, $formData, array_merge([
            'label'    => $formLabel,
            'required' => $formRequired,
        ], $formOptions));

        return $formBuilder;
    }

    /**
     * @param Field $field
     * @return string
     */
    public function getFormTab(Field $field)
    {
        return 'Common';
    }

    /**
     * @param Field $field
     * @return string
     */
    public function getFormGroup(Field $field)
    {
        return 'Common';
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getFormGroupOptions(Field $field)
    {
        return [];
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getFormOptions(Field $field)
    {
        return [];
    }

    /**
     * @param Field $field
     * @return array
     */
    private function getValidationConstraints(Field $field)
    {
        $constraints = [];
        if ($field->getRequired()) {
            $constraints[] = new NotBlank();
        }

        return $constraints;
    }
}