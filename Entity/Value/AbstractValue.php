<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Entity\Value;

use Glavweb\CompositeObjectBundle\Entity\Field;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractValue
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Table(name="composite_object_values")
 * @ORM\Entity(repositoryClass="Glavweb\CompositeObjectBundle\Repository\ValueRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "string"            = "ValueString",
 *     "text"              = "ValueText",
 *     "integer"           = "ValueInteger",
 *     "boolean"           = "ValueBoolean",
 *     "image"             = "ValueImage",
 *     "image_collection"  = "ValueImageCollection",
 *     "video"             = "ValueVideo",
 *     "video_collection"  = "ValueVideoCollection",
 *     "file"              = "ValueFile",
 *     "object"            = "ValueObject",
 *     "object_collection" = "ValueObjectCollection",
 *     "link"              = "ValueLink"
 * })
 */
abstract class AbstractValue implements ValueInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"comment": "ID of value"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var ObjectInstance
     *
     * @ORM\ManyToOne(targetEntity="Glavweb\CompositeObjectBundle\Entity\ObjectInstance", inversedBy="values")
     * @ORM\JoinColumn(name="instance_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $instance;

    /**
     * @var Field
     *
     * @ORM\ManyToOne(targetEntity="Glavweb\CompositeObjectBundle\Entity\Field", inversedBy="values")
     * @ORM\JoinColumn(name="field_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $field;

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
     * Set instance
     *
     * @param ObjectInstance $instance
     *
     * @return AbstractValue
     */
    public function setInstance(ObjectInstance $instance)
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * Get instance
     *
     * @return ObjectInstance
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Set field
     *
     * @param Field $field
     *
     * @return AbstractValue
     */
    public function setField(Field $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return Field
     */
    public function getField()
    {
        return $this->field;
    }
}
