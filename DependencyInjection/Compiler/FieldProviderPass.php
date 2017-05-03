<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged glavweb_datagrid.data_transformer services to FieldProviderRegister service.
 *
 * @package Glavweb\Glavweb\CompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FieldProviderPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('glavweb_cms_composite_object.field_provider_registry')) {
            return;
        }

        // Data transformers
        $providerRegistryDefinition = $container->getDefinition('glavweb_cms_composite_object.field_provider_registry');
        foreach ($container->findTaggedServiceIds('glavweb_cms_composite_object.field_provider') as $id => $tags) {
            if (!isset($tags[0]['field_type'])) {
                continue;
            }

            $providerRegistryDefinition->addMethodCall('set', [$tags[0]['field_type'], new Reference($id)]);
        }
    }
}
