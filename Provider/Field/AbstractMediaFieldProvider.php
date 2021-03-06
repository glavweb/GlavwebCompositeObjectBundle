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

use Glavweb\CompositeObjectBundle\Entity\Field;
use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\Manager\UploaderManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactory;

/**
 * Class AbstractFieldProvider
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class AbstractMediaFieldProvider extends AbstractFieldProvider
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var UploaderManager
     */
    protected $uploaderManager = null;

    /**
     * @var string
     */
    protected $context = null;

    /**
     * @var string
     */
    protected $thumbnailImagineFilter = null;

    /**
     * @var array
     */
    protected $imagineFilterSets;

    /**
     * AbstractMediaFieldProvider constructor.
     *
     * @param FormFactory $formFactory
     * @param Router $router
     * @param UploaderManager $uploaderManager
     * @param array $imagineFilterSets
     */
    public function __construct(FormFactory $formFactory, Router $router, UploaderManager $uploaderManager, array $imagineFilterSets = [])
    {
        parent::__construct($formFactory);

        $this->router = $router;
        $this->uploaderManager = $uploaderManager;
        $this->imagineFilterSets = $imagineFilterSets;
    }

    /**
     * @param string $requestId
     * @return Media[]
     * @throws \Exception
     */
    protected function handleUpload(string $requestId)
    {
        if (!$this->uploaderManager instanceof UploaderManager) {
            throw new \Exception('Uploader manager is not defined.');
        }

        $uploaderManager = $this->uploaderManager;

        $uploaderManager->removeMarkedMedia($requestId);
        $uploaderManager->renameMarkedMedia($requestId);
        $mediaEntities = $uploaderManager->uploadOrphans($requestId);

        return $mediaEntities;
    }

    /**
     * @param string $data
     * @return Media
     * @throws \Glavweb\UploaderBundle\Exception\ProviderNotFoundException
     */
    protected function createMedia($data)
    {
        $context = $this->getContext();
        $result  = $this->uploaderManager->upload($data, $context, md5(rand()));

        /** @var Media $media */
        $media = $result['media'];
        $media->setIsOrphan(false);

        return $media;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @param Field $field
     * @return string
     */
    public function getThumbnailImagineFilter(Field $field)
    {
        $className  = $field->getClass()->getName();
        $fieldName  = $field->getName();
        $filterName = sprintf('object_%s_%s', $className, $fieldName);

        if (isset($this->imagineFilterSets[$filterName])) {
            return $filterName;
        }

        return $this->thumbnailImagineFilter;
    }

    /**
     * @param string $thumbnailImagineFilter
     */
    public function setThumbnailImagineFilter($thumbnailImagineFilter)
    {
        $this->thumbnailImagineFilter = $thumbnailImagineFilter;
    }

    /**
     * @param Field $field
     * @return array
     */
    public function getFormOptions(Field $field)
    {
        return [
            'context'          => $this->getContext(),
            'thumbnail_filter' => $this->getThumbnailImagineFilter($field)
        ];
    }
}