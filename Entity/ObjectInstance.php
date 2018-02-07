<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Entity;

use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueObjectCollection;
use Glavweb\CompositeObjectBundle\Manager\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Glavweb\RestBundle\Mapping\Annotation as RestExtra;

/**
 * Class ObjectInstance
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Table(name="composite_object_instances")
 * @ORM\Entity
 * @ORM\EntityListeners({"Glavweb\CompositeObjectBundle\EventListener\ObjectInstanceListener"})
 *
 * @RestExtra\Rest(
 *     methods={"list", "view", "create", "update", "delete"}
 * )
 *
 * @UniqueEntity(fields={"class", "fixtureId"})
 */
class ObjectInstance
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"comment": "ID of instance"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false, options={"comment": "Position in list"})
     * @Gedmo\SortablePosition
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="fixture_id", type="string", nullable=true, options={"comment": "Fixture ID"})
     */
    private $fixtureId;

    /**
     * @var string
     *
     * @ORM\Column(name="mongodb_id", type="string", nullable=true, options={"comment": "MongoDB ID"})
     */
    private $mongodbId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true, options={"comment": "Time when was updating entity"})
     */
    private $updatedAt;

    /**
     * @var ObjectClass
     *
     * @ORM\ManyToOne(targetEntity="ObjectClass", inversedBy="instances")
     * @ORM\JoinColumn(name="class_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Gedmo\SortableGroup
     */
    private $class;

    /**
     * @var ObjectInstance
     *
     * @ORM\ManyToOne(targetEntity="ObjectInstance")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @Gedmo\SortableGroup
     */
    private $parent;

    /**
     * @var ValueObjectCollection
     *
     * @ORM\ManyToOne(targetEntity="Glavweb\CompositeObjectBundle\Entity\Value\ValueObjectCollection")
     * @ORM\JoinColumn(name="value_object_collection_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $valueObjectCollection;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue", mappedBy="instance", cascade={"persist", "remove"})
     */
    private $values;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->getId()) {
            $toStringTemplate = $this->getClass()->getToStringTemplate();
            if ($toStringTemplate && $this->getObjectManager()) {
                return $this->toStringByTemplate($toStringTemplate);
            }

            return '#' . (string)$this->getId();
        }

        return 'n/a';
    }

    /**
     * @param $template
     * @return mixed
     */
    public function toStringByTemplate($template)
    {
        return preg_replace_callback('/\{(.*?)\}/', function ($matches) {
            $fieldName = $matches[1];

            /** @var Field $field */
            $field = $this->getClass()->getFields()->filter(function (Field $field) use ($fieldName) {
                return $field->getName() == $fieldName;
            })->first();

            if ($field instanceof Field) {
                return (string)$this->getObjectManager()->getValueData($field, $this);
            }

            return '';

        }, $template);
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return ObjectInstance
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set fixtureId
     *
     * @param string $fixtureId
     *
     * @return ObjectInstance
     */
    public function setFixtureId($fixtureId)
    {
        $this->fixtureId = $fixtureId;

        return $this;
    }

    /**
     * Get fixtureId
     *
     * @return string
     */
    public function getFixtureId()
    {
        return $this->fixtureId;
    }

    /**
     * Set mongodbId
     *
     * @param string $mongodbId
     *
     * @return ObjectInstance
     */
    public function setMongodbId($mongodbId)
    {
        $this->mongodbId = $mongodbId;

        return $this;
    }

    /**
     * Get mongodbId
     *
     * @return string
     */
    public function getMongodbId()
    {
        return $this->mongodbId;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return ObjectInstance
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set class
     *
     * @param ObjectClass $class
     *
     * @return ObjectInstance
     */
    public function setClass(ObjectClass $class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return ObjectClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set parent
     *
     * @param ObjectInstance $parent
     *
     * @return ObjectInstance
     */
    public function setParent(ObjectInstance $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return ObjectInstance
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set valueObjectCollection
     *
     * @param ValueObjectCollection $valueObjectCollection
     *
     * @return ObjectInstance
     */
    public function setValueObjectCollection(ValueObjectCollection $valueObjectCollection = null)
    {
        $this->valueObjectCollection = $valueObjectCollection;

        return $this;
    }

    /**
     * Get valueObjectCollection
     *
     * @return ValueObjectCollection
     */
    public function getValueObjectCollection()
    {
        return $this->valueObjectCollection;
    }

    /**
     * Add value
     *
     * @param AbstractValue $value
     *
     * @return ObjectInstance
     */
    public function addValue(AbstractValue $value)
    {
        $this->values[] = $value;

        return $this;
    }

    /**
     * Remove value
     *
     * @param AbstractValue $value
     */
    public function removeValue(AbstractValue $value)
    {
        $this->values->removeElement($value);
    }

    /**
     * Get values
     *
     * @return ArrayCollection
     */
    public function getValues()
    {
        return $this->values;
    }
}
