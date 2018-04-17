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

use Ivory\CKEditorBundle\Form\Type\CKEditorType;

/**
 * Class WysiwygFieldProvider
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class WysiwygFieldProvider extends TextFieldProvider
{
    /**
     * @var string
     */
    protected $type = 'wysiwyg';

    /**
     * @return string
     */
    public function getFormType()
    {
        return CKEditorType::class;
    }
}