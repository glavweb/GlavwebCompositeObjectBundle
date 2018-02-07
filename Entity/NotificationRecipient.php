<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class NotificationRecipient
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Table(name="notification_recipients")
 * @ORM\Entity
 */
class NotificationRecipient
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"comment": "ID of recipient"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", nullable=false, options={"comment": "E-mail of recipient"})
     */
    private $email;

    /**
     * @var ObjectClass
     *
     * @ORM\ManyToOne(targetEntity="Glavweb\CompositeObjectBundle\Entity\ObjectClass", inversedBy="recipients")
     * @ORM\JoinColumn(name="class_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $class;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getEmail() ?: 'n/a';
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return NotificationRecipient
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set class
     *
     * @param ObjectClass $class
     *
     * @return NotificationRecipient
     */
    public function setClass(ObjectClass $class)
    {
        $class->addRecipient($this);
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return ObjectClass
     */
    public function getClass()
    {
        return $this->class;
    }
}
