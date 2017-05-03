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
use Glavweb\CompositeObjectBundle\Service\ApiDataManager;
use Glavweb\CompositeObjectBundle\Service\FixtureCreator;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Interface FieldProviderInterface
 *
 * @package Glavweb\CompositeObjectBundle\Provider\Field
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
interface FieldProviderInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * Create instance of Field and then store it in the Data Base.
     *
     * Call from the FixtureCreator.
     *
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
    public function createField($name, ObjectClass $class, ObjectClass $linkedClass = null, string $label = null, bool $required = null, bool $denormalized = null, bool $isList = null, bool $isFilter = null, array $options = []);

    /**
     * Create instance of Value and store it in the Data Base.
     *
     * Call from the ObjectManipulator (use from the FixtureCreator or ObjectInstanceAdmin).
     *
     * @param Field          $field
     * @param ObjectInstance $objectInstance
     * @param mixed|null     $data
     * @return AbstractValue
     */
    public function createValue(Field $field, ObjectInstance $objectInstance, $data = null);

    /**
     * Save the data to the Value (then will store it in the Data Base).
     *
     * Call from the ObjectManipulator (use from the API or Admin).
     *
     * @param AbstractValue $value
     * @param mixed $valueData
     * @param ObjectManipulator $objectManipulator
     */
    public function updateValue(AbstractValue $value, $valueData, ObjectManipulator $objectManipulator);

    /**
     * Create the value data by a fixture data.
     *
     * Then the data will use when for createValue().
     *
     * @param mixed          $data
     * @param Field          $field
     * @param FixtureCreator $fixtureCreator
     * @return mixed
     */
    public function createValueDataByFixture($data, Field $field, FixtureCreator $fixtureCreator);

    /**
     * Use for populate value.
     *
     * @todo Remove form interface
     *
     * @param AbstractValue $value
     * @param mixed $data
     */
    public function setValueData(AbstractValue $value, $data);

    /**
     * Returns the data of value.
     *
     * @param AbstractValue $value
     * @return mixed
     */
    public function getValueData(AbstractValue $value = null);

    /**
     * Returns data from form.
     *
     * Calls from createFormBuilder().
     *
     * @param AbstractValue $value
     * @return mixed
     */
    public function getFormData(AbstractValue $value = null);

    /**
     * Returns data for API.
     *
     * @param AbstractValue  $value
     * @param ApiDataManager $apiDataManager
     * @return mixed
     */
    public function getApiData(AbstractValue $value, ApiDataManager $apiDataManager);

    /**
     * Returns type of form.
     *
     * Calls from createFormBuilder().
     *
     * @return string
     */
    public function getFormType();

    /**
     * Returns options of form.
     *
     * Calls from createFormBuilder().
     *
     * @param Field $field
     * @return array
     */
    public function getFormOptions(Field $field);

    /**
     * Returns tab for admin interface.
     *
     * @param Field $field
     * @return string
     */
    public function getFormTab(Field $field);

    /**
     * Returns group for admin interface.
     *
     * @param Field $field
     * @return string
     */
    public function getFormGroup(Field $field);

    /**
     * Returns group options for admin interface.
     *
     * @param Field $field
     * @return array
     */
    public function getFormGroupOptions(Field $field);

    /**
     * Use for create value from Admin or API.
     *
     * @param Field         $field
     * @param AbstractValue $value
     * @param array         $options
     * @return FormBuilderInterface
     */
    public function createFormBuilder(Field $field, AbstractValue $value = null, $options = []);
}