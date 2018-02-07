<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\EventListener;

use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Manager\ObjectManager;
use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class MenuBuilderListener
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MenuBuilderListener
{
    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var null|string
     */
    private $adminGroup;

    /**
     * MenuBuilderListener constructor.
     *
     * @param RequestStack $requestStack
     * @param ObjectManager $objectManager
     * @param string|null $adminGroup
     */
    public function __construct(RequestStack $requestStack, ObjectManager $objectManager, string $adminGroup = null)
    {
        $this->request       = $requestStack->getCurrentRequest();
        $this->objectManager = $objectManager;
        $this->adminGroup    = $adminGroup;
    }


    /**
     * @param ConfigureMenuEvent $event
     */
    public function addMenuItems(ConfigureMenuEvent $event)
    {
        $currentClassId = null;
        if ($this->request) {
            $currentClassId = $this->request->get('class');
        }
        
        $menu = $event->getMenu();

        foreach ($this->objectManager->getGroupedObjectClasses() as $groupName => $objectClasses) {
            if ($this->adminGroup) {
                $groupName = $this->adminGroup;
            }

            $group = $menu->getChild($groupName);
            if (!$group) {
                $group = $menu->addChild($groupName, [
                    'label' => $groupName,
                ]);
            }

            foreach ($objectClasses as $objectClass) {
                /** @var ObjectClass $objectClass */
                $label = $objectClass->getLabel() ? $objectClass->getLabel() : $objectClass->getName();

                $child = $group->addChild($objectClass->getName(), [
                    'label' => $label,
                    'route' => 'composite_object_instance_list',
                    'routeParameters' => ['class' => $objectClass->getName()],
                    'routeAbsolute' => true
                ]);

                if ($objectClass->getId() == $currentClassId) {
                    $group->setExtra('active', true);
                    $child->setCurrent(true);
                }

            }
        }
    }
}