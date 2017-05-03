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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Glavweb\RestBundle\Mapping\Annotation as RestExtra;

/**
 * Class ObjectClass
 *
 * @package AppBundle\Entity
 * @author Andrey Nilov <nilov@glavweb.ru>
 *
 * @ORM\Table(name="composite_object_classes")
 * @ORM\Entity
 *
 * @RestExtra\Rest(
 *     methods={"list", "view", "create", "update", "delete"}
 * )
 */
class ObjectClass
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"comment": "ID класса объектов"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Название таблицы
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false, options={"comment": "Название класса"})
     * @Assert\NotBlank
     */
    private $name;

    /**
     * Лейбл класса
     *
     * @var string
     *
     * @ORM\Column(name="label", type="string", nullable=true, options={"comment": "Лейбл класса"})
     */
    private $label = null;

    /**
     * Template for toString() for instance
     *
     * @var string
     *
     * @ORM\Column(name="to_string_template", type="string", nullable=true, options={"comment": "Template for toString() for instance"})
     */
    private $toStringTemplate = null;

    /**
     * Лейбл класса
     *
     * @var string
     *
     * @ORM\Column(name="class_group", type="string", nullable=true, options={"comment": "Группа классов"})
     */
    private $group = null;

    /**
     * Является ли подклассом
     *
     * @var bool
     *
     * @ORM\Column(name="is_subclass", type="boolean", nullable=false, options={"comment": "Является ли подклассом"})
     */
    private $isSubclass = false;

    /**
     * Влкючена ли отправка уведомлений
     *
     * @var bool
     *
     * @ORM\Column(name="notification_enabled", type="boolean", nullable=false, options={"comment": "Влкючена ли отправка уведомлений"})
     */
    private $notificationEnabled = false;

    /**
     * @var array
     *
     * @ORM\Column(name="api_methods", type="array", nullable=false, options={"comment": "Доступные методы в API"})
     */
    private $apiMethods = ['view', 'list'];

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Field", mappedBy="class", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "DESC"})
     */
    private $fields;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Field", mappedBy="linkedClass")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $linkedFields;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ObjectInstance", mappedBy="class", cascade={"persist", "remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $instances;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="NotificationRecipient", mappedBy="class", cascade={"persist", "remove"})
     */
    private $recipients;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ?: 'n/a';
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fields       = new ArrayCollection();
        $this->linkedFields = new ArrayCollection();
        $this->instances    = new ArrayCollection();
        $this->recipients   = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return ObjectClass
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return ObjectClass
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set toStringTemplate
     *
     * @param string $toStringTemplate
     *
     * @return ObjectClass
     */
    public function setToStringTemplate($toStringTemplate)
    {
        $this->toStringTemplate = $toStringTemplate;

        return $this;
    }

    /**
     * Get toStringTemplate
     *
     * @return string
     */
    public function getToStringTemplate()
    {
        return $this->toStringTemplate;
    }

    /**
     * Set group
     *
     * @param string $group
     *
     * @return ObjectClass
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set isSubclass
     *
     * @param boolean $isSubclass
     *
     * @return ObjectClass
     */
    public function setIsSubclass($isSubclass)
    {
        $this->isSubclass = $isSubclass;

        return $this;
    }

    /**
     * Get isSubclass
     *
     * @return boolean
     */
    public function getIsSubclass()
    {
        return $this->isSubclass;
    }

    /**
     * Set notificationEnabled
     *
     * @param boolean $notificationEnabled
     *
     * @return ObjectClass
     */
    public function setNotificationEnabled($notificationEnabled)
    {
        $this->notificationEnabled = $notificationEnabled;

        return $this;
    }

    /**
     * Get notificationEnabled
     *
     * @return boolean
     */
    public function getNotificationEnabled()
    {
        return $this->notificationEnabled;
    }

    /**
     * Set apiMethods
     *
     * @param array $apiMethods
     *
     * @return ObjectClass
     */
    public function setApiMethods($apiMethods)
    {
        $this->apiMethods = $apiMethods;

        return $this;
    }

    /**
     * Get apiMethods
     *
     * @return array
     */
    public function getApiMethods()
    {
        return $this->apiMethods;
    }

    /**
     * Add field
     *
     * @param Field $field
     *
     * @return ObjectClass
     */
    public function addField(Field $field)
    {
        $field->setClass($this);
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Remove field
     *
     * @param Field $field
     */
    public function removeField(Field $field)
    {
        $this->fields->removeElement($field);
    }

    /**
     * Get fields
     *
     * @return ArrayCollection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Add linkedField
     *
     * @param Field $linkedField
     *
     * @return ObjectClass
     */
    public function addLinkedField(Field $linkedField)
    {
        $this->linkedFields[] = $linkedField;

        return $this;
    }

    /**
     * Remove linkedField
     *
     * @param Field $linkedField
     */
    public function removeLinkedField(Field $linkedField)
    {
        $this->linkedFields->removeElement($linkedField);
    }

    /**
     * Get linkedFields
     *
     * @return ArrayCollection
     */
    public function getLinkedFields()
    {
        return $this->linkedFields;
    }

    /**
     * Add instance
     *
     * @param ObjectInstance $instance
     *
     * @return ObjectClass
     */
    public function addInstance(ObjectInstance $instance)
    {
        $this->instances[] = $instance;

        return $this;
    }

    /**
     * Remove instance
     *
     * @param ObjectInstance $instance
     */
    public function removeInstance(ObjectInstance $instance)
    {
        $this->instances->removeElement($instance);
    }

    /**
     * Get instances
     *
     * @return ArrayCollection
     */
    public function getInstances()
    {
        return $this->instances;
    }

    /**
     * Add recipient
     *
     * @param NotificationRecipient $recipient
     *
     * @return ObjectClass
     */
    public function addRecipient(NotificationRecipient $recipient)
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * Remove recipient
     *
     * @param NotificationRecipient $recipient
     */
    public function removeRecipient(NotificationRecipient $recipient)
    {
        $this->recipients->removeElement($recipient);
    }

    /**
     * Get recipients
     *
     * @return ArrayCollection
     */
    public function getRecipients()
    {
        return $this->recipients;
    }
}
