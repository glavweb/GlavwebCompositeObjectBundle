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
 * Class ValueFloat
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Entity
 */
class ValueFloat extends AbstractValue
{
    /**
     * Value as float
     *
     * @var int
     *
     * @ORM\Column(name="value_float", type="float", nullable=false, options={"comment": "Float value"})
     * @Assert\NotBlank
     */
    private $float;

    /**
     * Set float
     *
     * @param float $float
     *
     * @return ValueFloat
     */
    public function setFloat($float)
    {
        $this->float = $float;

        return $this;
    }

    /**
     * Get float
     *
     * @return float
     */
    public function getFloat()
    {
        return $this->float;
    }
}
