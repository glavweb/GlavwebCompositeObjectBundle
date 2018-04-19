<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle;

/**
 * Class CompositeObjectEvents
 *
 * @package Glavweb\CompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
final class CompositeObjectEvents
{
    const PRE_PERSIST  = 'composite_object.pre_persist';
    const POST_PERSIST = 'composite_object.post_persist';
    const PRE_UPDATE   = 'composite_object.pre_update';
    const POST_UPDATE  = 'composite_object.post_update';
    const PRE_REMOVE   = 'composite_object.pre_remove';
    const POST_REMOVE  = 'composite_object.post_remove';
}
