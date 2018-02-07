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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ValueInteger
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Entity
 */
class ValueInteger extends AbstractValue
{
    /**
     * Значение как число
     *
     * @var int
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=false, options={"comment": "Integer value"})
     * @Assert\NotBlank
     */
    private $integer;

    /**
     * Set integer
     *
     * @param integer $integer
     *
     * @return ValueInteger
     */
    public function setInteger($integer)
    {
        $this->integer = $integer;

        return $this;
    }

    /**
     * Get integer
     *
     * @return integer
     */
    public function getInteger()
    {
        return $this->integer;
    }
}
