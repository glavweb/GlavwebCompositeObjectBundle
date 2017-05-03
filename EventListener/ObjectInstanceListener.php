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

use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Manager\ObjectManager;
use Glavweb\CompositeObjectBundle\Service\ApiDataManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Glavweb\MongoDBBundle\Registry;

/**
 * Class ObjectInstanceListener
 *
 * @package Glavweb\CompositeObjectBundle\EventListener
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ObjectInstanceListener
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ApiDataManager
     */
    private $apiDataManager;

    /**
     * @var Registry
     */
    private $mongodb;

    /**
     * ObjectInstanceListener constructor.
     *
     * @param ObjectManager $objectManager
     * @param ApiDataManager $apiDataManager
     * @param Registry $mongodb
     */
    public function __construct(ObjectManager $objectManager, ApiDataManager $apiDataManager, Registry $mongodb)
    {
        $this->objectManager  = $objectManager;
        $this->apiDataManager = $apiDataManager;
        $this->mongodb        = $mongodb;
    }

    /**
     * @ORM\PostLoad
     *
     * @param ObjectInstance $objectInstance
     * @param LifecycleEventArgs $event
     */
    public function postLoad(ObjectInstance $objectInstance, LifecycleEventArgs $event)
    {
        $objectInstance->setObjectManager($this->objectManager);
    }
}