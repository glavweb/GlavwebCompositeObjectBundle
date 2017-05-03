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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Glavweb\UploaderBundle\Entity\Media;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ValueImageCollection
 *
 * @package AppBundle\Entity
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Entity
 */
class ValueImageCollection extends AbstractValue
{
    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Glavweb\UploaderBundle\Entity\Media", orphanRemoval=true, cascade={"remove"})
     */
    private $medias;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->medias = new ArrayCollection();
    }

    /**
     * Add media
     *
     * @param Media $media
     *
     * @return ValueImageCollection
     */
    public function addMedia(Media $media)
    {
        $this->medias[] = $media;

        return $this;
    }

    /**
     * Remove media
     *
     * @param Media $media
     */
    public function removeMedia(Media $media)
    {
        $this->medias->removeElement($media);
    }

    /**
     * Get medias
     *
     * @return ArrayCollection
     */
    public function getMedias()
    {
        return $this->medias;
    }
}
