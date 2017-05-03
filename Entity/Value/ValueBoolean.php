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
 * Class ValueBoolean
 *
 * @package AppBundle\Entity
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Entity
 */
class ValueBoolean extends AbstractValue
{
    /**
     * Значение как логический тип
     *
     * @var boolean
     *
     * @ORM\Column(name="value_boolean", type="boolean", nullable=false, options={"comment": "Значение как логический тип"})
     * @Assert\NotBlank
     */
    private $boolean;

    /**
     * Set boolean
     *
     * @param boolean $boolean
     *
     * @return ValueBoolean
     */
    public function setBoolean($boolean)
    {
        $this->boolean = $boolean;

        return $this;
    }

    /**
     * Get boolean
     *
     * @return boolean
     */
    public function getBoolean()
    {
        return $this->boolean;
    }
}
