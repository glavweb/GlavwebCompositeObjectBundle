<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Controller;

use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Glavweb\CmsCoreBundle\Controller\CRUDController as BaseCRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CRUDController
 *
 * @package Glavweb\CompositeObjectBundle\Controller
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class CRUDController extends BaseCRUDController
{
    /**
     * Move element
     *
     * @param string $position
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function moveAction($position)
    {
        /** @var ObjectManipulator $objectManipulator */
        /** @var ObjectInstance $objectInstance */
        $objectManipulator = $this->get('glavweb_cms_composite_object.object_manipulator');
        $objectInstance = $this->admin->getSubject();

        $response = parent::moveAction($position);
        $objectManipulator->updatePositionsInMongoDBByClass($objectInstance->getClass());

        return $response;
    }
}