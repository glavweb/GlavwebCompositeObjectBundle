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
use Glavweb\CompositeObjectBundle\Entity\Value\ValueInteger;
use Glavweb\CompositeObjectBundle\Service\ApiDataManager;
use Glavweb\CompositeObjectBundle\Service\FixtureCreator;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * Class IntegerFieldProvider
 *
 * @package Glavweb\CompositeObjectBundle\Provider\Field
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class IntegerFieldProvider extends AbstractFieldProvider
{
    /**
     * @var string
     */
    protected $type = 'integer';

    /**
     * @param Field $field
     * @param ObjectInstance $objectInstance
     * @param null $data
     * @return ValueInteger
     * @throws \Exception
     */
    public function createValue(Field $field, ObjectInstance $objectInstance, $data = null)
    {
        $value = new ValueInteger();
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
        if (!$value instanceof ValueInteger) {
            throw new \Exception('Value must be instance of ValueInteger.');
        }

        $value->setInteger($valueData);
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
        if (!$value instanceof ValueInteger) {
            throw new \Exception('Value must be instance of ValueInteger.');
        }

        $value->setInteger($data);
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
        
        if (!$value instanceof ValueInteger) {
            throw new \Exception('Value must be instance of ValueInteger.');
        }

        return $value->getInteger();
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
        return IntegerType::class;
    }
}