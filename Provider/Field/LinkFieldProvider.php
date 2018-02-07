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
use Glavweb\CompositeObjectBundle\Entity\Value\ValueLink;
use Glavweb\CompositeObjectBundle\Service\ApiDataManager;
use Glavweb\CompositeObjectBundle\Service\FixtureCreator;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormFactory;

/**
 * Class LinkFieldProvider
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class LinkFieldProvider extends AbstractFieldProvider
{
    /**
     * @var string
     */
    protected $type = 'link';

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
     * @return ValueLink
     * @throws \Exception
     */
    public function createValue(Field $field, ObjectInstance $objectInstance, $data = null)
    {
        $value = new ValueLink();
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
        if (!$value instanceof ValueLink) {
            throw new \Exception('Value must be instance of ValueLink.');
        }

        $link = null;
        $linkId = $valueData;
        if ($linkId !== null) {
            $link = $this->doctrine->getRepository(ObjectInstance::class)->find($linkId);

            if (!$link instanceof ObjectInstance) {
                throw new \RuntimeException(sprintf('The link with ID "%s" is not found', $linkId));
            }
        }

        $value->setLink($link);
    }

    /**
     * @param mixed          $data
     * @param Field          $field
     * @param FixtureCreator $fixtureCreator
     * @return ObjectInstance
     */
    public function createValueDataByFixture($data, Field $field, FixtureCreator $fixtureCreator)
    {
        $fixtureId = $data;
        $link = $this->doctrine->getRepository(ObjectInstance::class)->findOneBy([
            'fixtureId' => $fixtureId
        ]);

        return $link;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $data
     * @throws \Exception
     */
    public function setValueData(AbstractValue $value, $data)
    {
        if (!$value instanceof ValueLink) {
            throw new \Exception('Value must be instance of ValueLink.');
        }

        $link = $data;
        if (!$link instanceof ObjectInstance) {
            $linkId = $data;
            $link = $this->doctrine->getRepository(ObjectInstance::class)->find($linkId);

            if (!$link instanceof ObjectInstance) {
                throw new \RuntimeException(sprintf('The link with ID "%s" is not found', $linkId));
            }
        }

        $value->setLink($data);
    }

    /**
     * @param AbstractValue $value
     * @return ObjectInstance
     * @throws \Exception
     */
    public function getValueData(AbstractValue $value = null)
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof ValueLink) {
            throw new \Exception('Value must be instance of ValueLink.');
        }

        return $value->getLink();
    }

    /**
     * @param AbstractValue $value
     * @return mixed
     * @throws \Exception
     */
    public function getFormData(AbstractValue $value = null)
    {
        if ($value !== null) {
            if (!$value instanceof ValueLink) {
                throw new \Exception('Value must be instance of ValueLink.');
            }

            if ($value->getLink() instanceof ObjectInstance) {
                return $value->getLink()->getId();
            }
        }

        return null;
    }

    /**
     * @param AbstractValue $value
     * @param ApiDataManager $apiDataManager
     * @return array
     * @throws \Exception
     */
    public function getApiData(AbstractValue $value, ApiDataManager $apiDataManager)
    {
        if (!$value instanceof ValueLink) {
            throw new \Exception('Value must be instance of ValueLink.');
        }

        $data = null;
        $link = $value->getLink();
        if ($link instanceof ObjectInstance) {
            $data = [
                '_string' => (string)$link
            ];

            if ($value->getField()->isDenormalized()) {
                $data = array_merge($data, $apiDataManager->getObjectData($link));

            } else {
                $data['id'] = $link->getId();
            }
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return ChoiceType::class;
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getFormOptions(Field $field)
    {
        $choices  = $this->getChoices($field);

        return [
            'choices' => $choices
        ];
    }

    /**
     * @param Field $field
     * @return array
     */
    private function getChoices(Field $field)
    {
        $class = $field->getLinkedClass();
        $instances = $class->getInstances();

        $choices = [];
        foreach ($instances as $instance) {
            /** @var ObjectInstance $instance */
            $choices[(string)$instance] = $instance->getId();
        }

        return $choices;
    }
}