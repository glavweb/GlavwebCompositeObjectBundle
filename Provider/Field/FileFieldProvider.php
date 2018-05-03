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
use Glavweb\CompositeObjectBundle\Entity\Value\ValueFile;
use Glavweb\CompositeObjectBundle\Service\ApiDataManager;
use Glavweb\CompositeObjectBundle\Service\FixtureCreator;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Glavweb\UploaderDropzoneBundle\Form\FileType;
use Glavweb\UploaderBundle\Util\MediaStructure;
use Glavweb\UploaderBundle\Entity\Media;
use Glavweb\UploaderBundle\Manager\UploaderManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactory;

/**
 * Class FileFieldProvider
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class FileFieldProvider extends AbstractMediaFieldProvider
{
    /**
     * @var string
     */
    protected $type = 'file';

    /**
     * @var string
     */
    protected $context = 'content_file';

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
     */
    public function __construct(FormFactory $formFactory, Router $router, UploaderManager $uploaderManager, MediaStructure $mediaStructure)
    {
        parent::__construct($formFactory, $router, $uploaderManager, []);

        $this->mediaStructure = $mediaStructure;
    }

    /**
     * @param Field $field
     * @param ObjectInstance $objectInstance
     * @param null $data
     * @return ValueFile
     * @throws \Exception
     */
    public function createValue(Field $field, ObjectInstance $objectInstance, $data = null)
    {
        $value = new ValueFile();
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
        if (!$value instanceof ValueFile) {
            throw new \Exception('Value must be instance of ValueFile.');
        }

        $value->setMedia($valueData);
    }

    /**
     * @param mixed          $data
     * @param Field          $field
     * @param FixtureCreator $fixtureCreator
     * @return Media
     */
    public function createValueDataByFixture($data, Field $field, FixtureCreator $fixtureCreator)
    {
        $media = $this->createMedia($data);

        return $media;
    }

    /**
     * @param AbstractValue $value
     * @param mixed $data
     * @throws \Exception
     */
    public function setValueData(AbstractValue $value, $data)
    {
        if (!$value instanceof ValueFile) {
            throw new \Exception('Value must be instance of ValueFile.');
        }

        $value->setMedia($data);
    }

    /**
     * @param AbstractValue $value
     * @return Media
     * @throws \Exception
     */
    public function getValueData(AbstractValue $value = null)
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof ValueFile) {
            throw new \Exception('Value must be instance of ValueFile.');
        }

        return $value->getMedia();
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

        $media = $this->getValueData($value);

        return $media;
    }
    /**
     * @param AbstractValue  $value
     * @param ApiDataManager $apiDataManager
     * @return array
     */
    public function getApiData(AbstractValue $value, ApiDataManager $apiDataManager)
    {
        $mediaStructure = $this->mediaStructure;

        $media = $this->getValueData($value);
        $data = $media ? current($mediaStructure->getStructure([$media], null, false, true)) : [];

        return $data;
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return FileType::class;
    }
}