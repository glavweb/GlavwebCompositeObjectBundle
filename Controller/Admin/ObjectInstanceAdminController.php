<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Controller\Admin;

use Glavweb\CompositeObjectBundle\Controller\CRUDController;
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ObjectInstanceAdminController
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ObjectInstanceAdminController extends CRUDController
{
    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateMongodbAction(Request $request)
    {
        $objectManipulator = $this->get('glavweb_cms_composite_object.object_manipulator');
        $objectClassRepository = $this->getDoctrine()->getRepository(ObjectClass::class);
        $className = $request->get('class');

        try {
            $objectClass = $objectClassRepository->findOneBy(['name' => $className]);
            if (!$objectClass instanceof ObjectClass) {
                throw new \RuntimeException('Object class not found by name "' . $className . '".');
            }

            $objectManipulator->updateInMongoDBByClass($objectClass);
            $this->addFlash('sonata_flash_success', $this->trans('flash_update_mongo_db_success'));

        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', $this->trans('flash_update_mongo_db_error'));
        }

        return new RedirectResponse($this->admin->generateUrl(
            'list',
            array('filter' => $this->admin->getFilterParameters())
        ));
    }
}