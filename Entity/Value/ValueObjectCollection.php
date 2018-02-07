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

use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ValueObjectCollection
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Entity
 */
class ValueObjectCollection extends AbstractValue
{
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Glavweb\CompositeObjectBundle\Entity\ObjectInstance", mappedBy="valueObjectCollection")
     * @ORM\OrderBy({"position" = "DESC"})
     */
    private $objects;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->objects = new ArrayCollection();
    }

    /**
     * Add object
     *
     * @param ObjectInstance $object
     *
     * @return ValueObjectCollection
     */
    public function addObject(ObjectInstance $object)
    {
        $object->setValueObjectCollection($this);
        $this->objects[] = $object;

        return $this;
    }

    /**
     * Remove object
     *
     * @param ObjectInstance $object
     */
    public function removeObject(ObjectInstance $object)
    {
        $this->objects->removeElement($object);
    }

    /**
     * Get objects
     *
     * @return ArrayCollection
     */
    public function getObjects()
    {
        return $this->objects;
    }
}
