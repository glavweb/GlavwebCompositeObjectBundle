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

use Glavweb\CaptchaBundle\Controller\CaptchaController;
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Generates a captcha via a URL
 *
 * @package Glavweb\CompositeObjectBundle\Controller\Api
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class CaptchaApiController extends CaptchaController
{
    /**
     * Action that is used to generate the captcha for class, save its code, and stream the image
     *
     * @ApiDoc(
     *     views={"default", "composite-object"},
     *     section="Composite Object API",
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when an error has occurred",
     *     }
     * )
     *
     * @Route("composite-object-captcha/{class}/{token}", name="api_composite_object_captcha_generate_captcha", defaults={"_format": "json"}, methods={"GET"})
     *
     * @ParamConverter("class", options={"mapping": {"class": "name"}})
     *
     * @Rest\QueryParam(name="width", nullable=true, description="width")
     * @Rest\QueryParam(name="height", nullable=true, description="height")
     * @Rest\QueryParam(name="text_color", nullable=true, description="text_color")
     * @Rest\QueryParam(name="background_color", nullable=true, description="background_color")
     * @Rest\QueryParam(name="background_images", nullable=true, description="background_images")
     * @Rest\QueryParam(name="max_front_lines", nullable=true, description="max_front_lines")
     * @Rest\QueryParam(name="max_behind_lines", nullable=true, description="max_behind_lines")
     * @Rest\QueryParam(name="interpolation", nullable=true, description="interpolation")
     * @Rest\QueryParam(name="invalid_message", nullable=true, description="invalid_message")
     *
     * @param ObjectClass $class
     * @param string $token
     * @param Request $request
     * @return Response
     */
    public function generateCaptchaForClassAction(ObjectClass $class, string $token, Request $request): Response
    {
        $classOptions   = $class->getCaptchaOptions();
        $defaultOptions = array_merge($this->getParameter('glavweb_captcha.config'), $classOptions);

        $options = array_merge($defaultOptions, $this->getFilteredRequestOptions($request));

        if (!$class->isCaptchaEnabled()) {
            return $this->errorResponse($options);
        }

        return $this->doGenerateCaptchaAction($token, $options);
    }
}
