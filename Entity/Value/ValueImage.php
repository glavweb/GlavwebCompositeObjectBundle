<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Entity\Value;

use Doctrine\ORM\Mapping as ORM;
use Glavweb\UploaderBundle\Entity\Media;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ValueImage
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Entity
 */
class ValueImage extends AbstractValue
{
    /**
     * @var Media
     *
     * @ORM\OneToOne(targetEntity="Glavweb\UploaderBundle\Entity\Media", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $media;

    /**
     * Set media
     *
     * @param Media $media
     *
     * @return ValueImage
     */
    public function setMedia(Media $media = null)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }
}
