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
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Glavweb\CompositeObjectBundle\Entity\Value\ValueVideoCollection;
use Glavweb\CompositeObjectBundle\Service\ApiDataManager;
use Glavweb\CompositeObjectBundle\Service\FixtureCreator;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Glavweb\UploaderDropzoneBundle\Form\VideoCollectionType;
use Glavweb\UploaderBundle\Util\MediaStructure;
use Doctrine\Common\Collections\ArrayCollection;
use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\Manager\UploaderManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactory;

/**
 * Class VideoCollectionFieldProvider
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class VideoCollectionFieldProvider extends AbstractMediaFieldProvider
{
    /**
     * @var string
     */
    protected $type = 'video_collection';

    /**
     * @var string
     */
    protected $context = 'content_video';

    /**
     * @var string
     */
    protected $thumbnailImagineFilter = 'glavweb_cms_core_video_gallery';

    /**
     * @var MediaStructure
     */
    private $mediaStructure;

    /**
     * ImageFieldProvider constructor.
     *
     * @param FormFactory $formFactory
     * @param Router $router
     * @param UploaderManager $uploaderManager
     * @param MediaStructure $mediaStructure
     * @param array $imagineFilterSets
     */
    public function __construct(FormFactory $formFactory, Router $router, UploaderManager $uploaderManager, MediaStructure $mediaStructure, array $imagineFilterSets = [])
    {
        parent::__construct($formFactory, $router, $uploaderManager, $imagineFilterSets);

        $this->mediaStructure = $mediaStructure;
    }

    /**
     * @param Field $field
     * @param ObjectInstance $objectInstance
     * @param null $data
     * @return ValueVideoCollection
     * @throws \Exception
     */
    public function createValue(Field $field, ObjectInstance $objectInstance, $data = null)
    {
        $value = new ValueVideoCollection();
        $this->populateValue($value, $field, $objectInstance, $data);

        return $value;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $valueData
     * @param ObjectManipulator $objectManipulator
     * @throws \Exception
     */
    public function updateValue(AbstractValue $value, $valueData, ObjectManipulator $objectManipulator)
    {
        if (!$value instanceof ValueVideoCollection) {
            throw new \Exception('Value must be instance of ValueVideoCollection.');
        }

        $requestId = $valueData;

        $mediaEntities = $this->handleUpload($requestId);
        foreach ($mediaEntities as $mediaEntity) {
            if (!$value->getMedias()->contains($mediaEntity)) {
                $value->addMedia($mediaEntity);
            }
        }
    }

    /**
     * @param mixed          $data
     * @param Field          $field
     * @param FixtureCreator $fixtureCreator
     * @return Media[]
     */
    public function createValueDataByFixture($data, Field $field, FixtureCreator $fixtureCreator)
    {
        $medias = [];
        foreach ((array)$data as $item) {
            $medias[] = $this->createMedia($item);
        }

        return $medias;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $data
     * @throws \Exception
     */
    public function setValueData(AbstractValue $value, $data)
    {
        if (!$value instanceof ValueVideoCollection) {
            throw new \Exception('Value must be instance of ValueVideoCollection.');
        }

        foreach ($data as $media) {
            $value->addMedia($media);
        }
    }

    /**
     * @param AbstractValue $value
     * @return Media[]
     * @throws \Exception
     */
    public function getValueData(AbstractValue $value = null)
    {
        if ($value === null) {
            return [];
        }

        if (!$value instanceof ValueVideoCollection) {
            throw new \Exception('Value must be instance of ValueVideoCollection.');
        }

        return $value->getMedias();
    }

    /**
     * @param AbstractValue $value
     * @return mixed
     */
    public function getFormData(AbstractValue $value = null)
    {
        if ($value === null) {
            return [];
        }

        /** @var ArrayCollection $medias */
        $medias = $this->getValueData($value);

        return $medias;
    }

    /**
     * @param AbstractValue  $value
     * @param ApiDataManager $apiDataManager
     * @return array
     */
    public function getApiData(AbstractValue $value, ApiDataManager $apiDataManager)
    {
        $mediaStructure = $this->mediaStructure;

        /** @var ArrayCollection $medias */
        $medias = $this->getValueData($value);
        $data = $medias ? $mediaStructure->getStructure($medias->toArray()) : [];

        return $data;
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return VideoCollectionType::class;
    }
}