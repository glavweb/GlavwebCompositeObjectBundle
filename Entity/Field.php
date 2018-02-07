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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Fresh\DoctrineEnumBundle\Validator\Constraints\Enum as EnumAssert;
use Gedmo\Mapping\Annotation as Gedmo;
use Glavweb\RestBundle\Mapping\Annotation as RestExtra;

/**
 * Class Field
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Table(name="composite_object_fields")
 * @ORM\Entity
 *
 * @RestExtra\Rest(
 *     methods={"list", "view", "create", "update", "delete"}
 * )
 */
class Field
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"comment": "ID of field"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false, options={"comment": "Position is list"})
     * @Gedmo\SortablePosition
     */
    private $position = 0;

    /**
     * @var int
     */
    private $plainPosition;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="CompositeObjectFieldType", length=255, nullable=false, options={"comment": "Type of field"})
     * @EnumAssert(entity="Glavweb\CompositeObjectBundle\DBAL\Types\Object\CompositeObjectFieldType")
     * @Assert\NotBlank
     */
    private $type;

    /**
     * Название поля
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false, options={"comment": "System name"})
     * @Assert\NotBlank
     */
    private $name;

    /**
     * Лейбл поля
     *
     * @var string
     *
     * @ORM\Column(name="label", type="string", nullable=true, options={"comment": "Label"})
     */
    private $label = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="required", type="boolean", nullable=true, options={"comment": "Required for form?"})
     */
    private $required = false;

    /**
     * Is denormalized linked data?
     *
     * @var bool
     *
     * @ORM\Column(name="denormalized", type="boolean", nullable=true, options={"comment": "Is denormalized linked data?"})
     */
    private $denormalized = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_list", type="boolean", nullable=true, options={"comment": "Is in list?"})
     */
    private $isList = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_filter", type="boolean", nullable=true, options={"comment": "Is in filter?"})
     */
    private $isFilter = false;

    /**
     * Дополнительные опции к полю
     *
     * @var bool
     *
     * @ORM\Column(name="options", type="array", nullable=true, options={"comment": "Additional options"})
     */
    private $options = [];

    /**
     * @var Object
     *
     * @ORM\ManyToOne(targetEntity="ObjectClass", inversedBy="fields")
     * @ORM\JoinColumn(name="class_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Gedmo\SortableGroup
     */
    private $class;

    /**
     * @var Object
     *
     * @ORM\ManyToOne(targetEntity="ObjectClass", inversedBy="linkedFields")
     * @ORM\JoinColumn(name="linked_class_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $linkedClass;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue", mappedBy="field")
     */
    private $values;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
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
     * @return Field
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
     * Set plain position
     *
     * @param integer $position
     *
     * @return Field
     */
    public function setPlainPosition($position)
    {
        $this->plainPosition = $position;

        return $this;
    }

    /**
     * Get plain position
     *
     * @return integer
     */
    public function getPlainPosition()
    {
        return $this->plainPosition;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Field
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Field
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Field
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set required
     *
     * @param boolean $required
     *
     * @return Field
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get required
     *
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Get required
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->getRequired();
    }

    /**
     * Set denormalized
     *
     * @param boolean $denormalized
     *
     * @return Field
     */
    public function setDenormalized($denormalized)
    {
        $this->denormalized = $denormalized;

        return $this;
    }

    /**
     * Get denormalized
     *
     * @return boolean
     */
    public function getDenormalized()
    {
        return $this->denormalized;
    }

    /**
     * Get denormalized
     *
     * @return boolean
     */
    public function isDenormalized()
    {
        return $this->getDenormalized();
    }

    /**
     * Set isList
     *
     * @param boolean $isList
     *
     * @return Field
     */
    public function setIsList($isList)
    {
        $this->isList = $isList;

        return $this;
    }

    /**
     * Get isList
     *
     * @return boolean
     */
    public function getIsList()
    {
        return $this->isList;
    }

    /**
     * Set isFilter
     *
     * @param boolean $isFilter
     *
     * @return Field
     */
    public function setIsFilter($isFilter)
    {
        $this->isFilter = $isFilter;

        return $this;
    }

    /**
     * Get isFilter
     *
     * @return boolean
     */
    public function getIsFilter()
    {
        return $this->isFilter;
    }

    /**
     * Set options
     *
     * @param array $options
     *
     * @return Field
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set class
     *
     * @param \Glavweb\CompositeObjectBundle\Entity\ObjectClass $class
     *
     * @return Field
     */
    public function setClass(ObjectClass $class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return \Glavweb\CompositeObjectBundle\Entity\ObjectClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set linkedClass
     *
     * @param ObjectClass $linkedClass
     *
     * @return Field
     */
    public function setLinkedClass(ObjectClass $linkedClass = null)
    {
        $this->linkedClass = $linkedClass;

        return $this;
    }

    /**
     * Get linkedClass
     *
     * @return ObjectClass
     */
    public function getLinkedClass()
    {
        return $this->linkedClass;
    }

    /**
     * Add value
     *
     * @param AbstractValue $value
     *
     * @return Field
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
