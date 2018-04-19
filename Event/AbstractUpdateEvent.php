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

/**
 * Class AbstractUpdateEvent
 *
 * @package Glavweb\CompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class AbstractUpdateEvent extends AbstractEvent
{
    /**
     * @var array
     */
    protected $data;

    /**
     * PostUpdateEvent constructor.
     *
     * @param ObjectInstance $object
     * @param array $data
     */
    public function __construct(ObjectInstance $object, array $data = [])
    {
        parent::__construct($object);

        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}