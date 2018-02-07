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

/**
 * Class ValueLink
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Entity
 */
class ValueLink extends AbstractValue
{
    /**
     * @var ObjectInstance
     *
     * @ORM\ManyToOne(targetEntity="Glavweb\CompositeObjectBundle\Entity\ObjectInstance")
     * @ORM\JoinColumn(name="value_link_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $link;

    /**
     * Set link
     *
     * @param \Glavweb\CompositeObjectBundle\Entity\ObjectInstance $link
     *
     * @return ValueLink
     */
    public function setLink(ObjectInstance $link = null)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return \Glavweb\CompositeObjectBundle\Entity\ObjectInstance
     */
    public function getLink()
    {
        return $this->link;
    }
}
