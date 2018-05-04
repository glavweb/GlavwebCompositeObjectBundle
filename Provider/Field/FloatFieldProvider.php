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
use Glavweb\CompositeObjectBundle\Entity\Value\ValueFloat;
use Glavweb\CompositeObjectBundle\Service\ApiDataManager;
use Glavweb\CompositeObjectBundle\Service\FixtureCreator;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * Class FloatFieldProvider
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FloatFieldProvider extends AbstractFieldProvider
{
    /**
     * @var string
     */
    protected $type = 'float';

    /**
     * @param Field $field
     * @param ObjectInstance $objectInstance
     * @param null $data
     * @return ValueFloat
     * @throws \Exception
     */
    public function createValue(Field $field, ObjectInstance $objectInstance, $data = null)
    {
        $value = new ValueFloat();
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
        if (!$value instanceof ValueFloat) {
            throw new \Exception('Value must be instance of ValueFloat.');
        }

        $value->setFloat($valueData);
    }

    /**
     * @param mixed          $data
     * @param Field          $field
     * @param FixtureCreator $fixtureCreator
     * @return int
     */
    public function createValueDataByFixture($data, Field $field, FixtureCreator $fixtureCreator)
    {
        return (int)$data;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $data
     * @throws \Exception
     */
    public function setValueData(AbstractValue $value, $data)
    {
        if (!$value instanceof ValueFloat) {
            throw new \Exception('Value must be instance of ValueFloat.');
        }

        $value->setFloat($data);
    }

    /**
     * @param AbstractValue $value
     * @return int
     * @throws \Exception
     */
    public function getValueData(AbstractValue $value = null)
    {
        if ($value === null) {
            return null;
        }
        
        if (!$value instanceof ValueFloat) {
            throw new \Exception('Value must be instance of ValueFloat.');
        }

        return $value->getFloat();
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
     * @return int
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
        return NumberType::class;
    }
}