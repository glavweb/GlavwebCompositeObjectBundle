<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Block;

use Glavweb\CompositeObjectBundle\Manager\ObjectManager;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AdminListBlockService
 *
 * @package Glavweb\CompositeObjectBundle\Block
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class AdminListBlockService extends AbstractAdminBlockService
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param string $name
     * @param EngineInterface $templating
     * @param ObjectManager $objectManager
     */
    public function __construct($name, EngineInterface $templating, ObjectManager $objectManager)
    {
        parent::__construct($name, $templating);

        $this->objectManager = $objectManager;
    }

    /**
     * @param BlockContextInterface $blockContext
     * @param Response $response
     * @return Response
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $groupedObjectClasses = $this->objectManager->getGroupedObjectClasses();

        return $this->renderResponse($blockContext->getTemplate(), array(
            'groupedObjectClasses' => $groupedObjectClasses,
            'block'                => $blockContext->getBlock(),
            'settings'             => $blockContext->getSettings()
        ), $response);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'template' => 'GlavwebCompositeObjectBundle:blocks:block_admin_list.html.twig'
        ));
    }
}