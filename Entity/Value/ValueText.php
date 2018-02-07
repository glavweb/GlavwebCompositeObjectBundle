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
class ValueText extends AbstractValue
{
    /**
     * Значение
     *
     * @var string
     *
     * @ORM\Column(name="value_text", type="text", nullable=false, options={"comment": "Text value"})
     * @Assert\NotBlank
     */
    private $text;

    /**
     * Set text
     *
     * @param string $text
     *
     * @return ValueText
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}
