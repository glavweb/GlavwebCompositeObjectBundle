<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Controller\Api;

use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Form\FixtureInstanceType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Glavweb\RestBundle\Controller\GlavwebRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Glavweb\CompositeObjectBundle\Form\FixtureType as FixtureFormType;

/**
 * Class FixtureApiController
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FixtureApiController extends GlavwebRestController
{
    /**
     * Create fixture
     *
     * @ApiDoc(
     *     views={"default", "fixture"},
     *     section="Fixture API",
     *     input={"class"="Glavweb\CompositeObjectBundle\Form\FixtureType", "name"=""},
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when an error has occurred",
     *     }
     * )
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @Route("fixtures", name="api_fixture_create_fixture", defaults={"_format": "json"}, methods={"POST"})
     *
     * @param Request $request A Symfony request
     */
    public function createFixtureAction(Request $request)
    {
        $form = $this->get('form.factory')->createNamed(
            null,
            FixtureFormType::class,
            null,
            ['csrf_protection' => false]
        );

        $form->handleRequest($request);
        $data = json_decode($form->get('data')->getData(), true);

        $fixtureCreator = $this->get('glavweb_cms_composite_object.fixture_creator');
        $fixtureCreator->create($data, true);
    }

    /**
     * Create fixture instance
     *
     * @ApiDoc(
     *     views={"default", "fixture"},
     *     section="Fixture API",
     *     input={"class"="Glavweb\CompositeObjectBundle\Form\FixtureType", "name"=""},
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when an error has occurred",
     *     }
     * )
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @Route("fixtures/instance/{className}", name="api_fixture_create_fixture_instance", defaults={"_format": "json"}, methods={"POST"})
     *
     * @param Request $request A Symfony request
     * @param string $className
     */
    public function createFixtureInstanceAction(Request $request, $className)
    {
        $class = $this->getDoctrine()->getManager()->getRepository(ObjectClass::class)->findOneBy([
            'name' => $className
        ]);

        if (!$class instanceof ObjectClass) {
            throw $this->createNotFoundException();
        }

        $form = $this->get('form.factory')->createNamed(
            null,
            FixtureInstanceType::class,
            null,
            ['csrf_protection' => false]
        );

        $form->handleRequest($request);

        $fixtureId = $form->get('fixtureId')->getData();
        $data      = json_decode($form->get('data')->getData(), true);

        $fixtureCreator = $this->get('glavweb_cms_composite_object.fixture_creator');
        $fixtureCreator->createInstance($class, $data, $fixtureId);
    }
}