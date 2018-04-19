<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Event;

use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AbstractEvent
 *
 * @package Glavweb\CompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class AbstractEvent extends Event
{
    /**
     * @var ObjectInstance
     */
    protected $object;

    /**
     * PostPersistEvent constructor.
     *
     * @param ObjectInstance $object
     */
    public function __construct(ObjectInstance $object)
    {
        $this->object = $object;
    }

    /**
     * @return ObjectInstance
     */
    public function getObject(): ObjectInstance
    {
        return $this->object;
    }
}