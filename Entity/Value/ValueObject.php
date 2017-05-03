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
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ValueObject
 *
 * @package AppBundle\Entity
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Entity
 */
class ValueObject extends AbstractValue
{
    /**
     * @var ObjectInstance
     *
     * @ORM\OneToOne(targetEntity="Glavweb\CompositeObjectBundle\Entity\ObjectInstance")
     * @ORM\JoinColumn(name="value_object_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $object;

    /**
     * Set object
     *
     * @param ObjectInstance $object
     *
     * @return ValueObject
     */
    public function setObject(ObjectInstance $object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return ObjectInstance
     */
    public function getObject()
    {
        return $this->object;
    }
}
