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
 * @package GlavwebCompositeObjectBundle
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
        self::STRING            => 'composite_object_field_type.string',
        self::TEXT              => 'composite_object_field_type.text',
        self::INTEGER           => 'composite_object_field_type.integer',
        self::BOOLEAN           => 'composite_object_field_type.boolean',
        self::IMAGE             => 'composite_object_field_type.image',
        self::IMAGE_COLLECTION  => 'composite_object_field_type.image_collection',
        self::VIDEO             => 'composite_object_field_type.video',
        self::VIDEO_COLLECTION  => 'composite_object_field_type.video_collection',
        self::FILE              => 'composite_object_field_type.file',
        self::OBJECT            => 'composite_object_field_type.object',
        self::OBJECT_COLLECTION => 'composite_object_field_type.object_collection',
        self::LINK              => 'composite_object_field_type.link'
    );
}