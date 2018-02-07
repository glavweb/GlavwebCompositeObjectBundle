<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Provider\Field;

/**
 * Class FieldProviderRegistry
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FieldProviderRegistry
{
    /**
     * @var array
     */
    private $providers = [];

    /**
     * @param string $type
     * @param FieldProviderInterface $provider
     * @return $this
     */
    public function set($type, FieldProviderInterface $provider)
    {
        $this->providers[$type] = $provider;

        return $this;
    }

    /**
     * @param string $type
     * @return FieldProviderInterface
     * @throws \Exception
     */
    public function get($type)
    {
        if (!isset($this->providers[$type])) {
            throw new \Exception('Provider "' . $type . '" not found.');
        }

        return $this->providers[$type];
    }
}