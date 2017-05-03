<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\DBAL\Types\Object;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * Class CompositeObjectFieldType
 *
 * @package Glavweb\CompositeObjectBundle\DBAL\Types\Object
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class CompositeObjectFieldType extends AbstractEnumType
{
    const STRING            = 'string';
    const TEXT              = 'text';
    const INTEGER           = 'integer';
    const BOOLEAN           = 'boolean';
    const IMAGE             = 'image';
    const IMAGE_COLLECTION  = 'image_collection';
    const VIDEO             = 'video';
    const VIDEO_COLLECTION  = 'video_collection';
    const FILE              = 'file';
    const OBJECT            = 'object';
    const OBJECT_COLLECTION = 'object_collection';
    const LINK              = 'link';

    /**
     * @var array
     */
    protected static $choices = array(
        self::STRING            => 'Строка',
        self::TEXT              => 'Текст',
        self::INTEGER           => 'Число',
        self::BOOLEAN           => 'Логический тип',
        self::IMAGE             => 'Изображение',
        self::IMAGE_COLLECTION  => 'Коллекция изображений',
        self::VIDEO             => 'Видео',
        self::VIDEO_COLLECTION  => 'Коллекция видео',
        self::FILE              => 'Файл',
        self::OBJECT            => 'Объект',
        self::OBJECT_COLLECTION => 'Коллекция объектов',
        self::LINK              => 'Ссылка на объект',
    );
}