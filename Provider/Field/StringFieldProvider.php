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
use Glavweb\CompositeObjectBundle\Entity\Value\ValueString;
use Glavweb\CompositeObjectBundle\Service\ApiDataManager;
use Glavweb\CompositeObjectBundle\Service\FixtureCreator;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class StringFieldProvider
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class StringFieldProvider extends AbstractFieldProvider
{
    /**
     * @var string
     */
    protected $type = 'string';

    /**
     * @param Field $field
     * @param ObjectInstance $objectInstance
     * @param null $data
     * @return ValueString
     * @throws \Exception
     */
    public function createValue(Field $field, ObjectInstance $objectInstance, $data = null)
    {
        $value = new ValueString();
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
        if (!$value instanceof ValueString) {
            throw new \Exception('Value must be instance of ValueString.');
        }

        $value->setString($valueData);
    }

    /**
     * @param mixed          $data
     * @param Field          $field
     * @param FixtureCreator $fixtureCreator
     * @return string
     */
    public function createValueDataByFixture($data, Field $field, FixtureCreator $fixtureCreator)
    {
        return (string)$data;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $data
     * @throws \Exception
     */
    public function setValueData(AbstractValue $value, $data)
    {
        if (!$value instanceof ValueString) {
            throw new \Exception('Value must be instance of ValueString.');
        }

        $value->setString($data);
    }

    /**
     * @param AbstractValue $value
     * @return string
     * @throws \Exception
     */
    public function getValueData(AbstractValue $value = null)
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof ValueString) {
            throw new \Exception('Value must be instance of ValueString.');
        }

        return $value->getString();
    }

    /**
     * @param AbstractValue $value
     * @return mixed
     */
    public function getFormData(AbstractValue $value = null)
    {
        return $this->getValueData($value);
    }

    /**
     * @param AbstractValue  $value
     * @param ApiDataManager $apiDataManager
     * @return bool
     */
    public function getApiData(AbstractValue $value, ApiDataManager $apiDataManager)
    {
        return $this->getValueData($value);
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return TextType::class;
    }
}