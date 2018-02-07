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
 * Class Value
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Entity
 */
class ValueString extends AbstractValue
{
    /**
     * Значение
     *
     * @var string
     *
     * @ORM\Column(name="value_string", type="string", nullable=false, options={"comment": "String value"})
     * @Assert\NotBlank
     */
    private $string;

    /**
     * Set string
     *
     * @param string $string
     *
     * @return ValueString
     */
    public function setString($string)
    {
        $this->string = $string;

        return $this;
    }

    /**
     * Get string
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }
}
