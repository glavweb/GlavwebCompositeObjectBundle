<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Controller\Api;

use Glavweb\CompositeObjectBundle\Entity\Field;
use Glavweb\CompositeObjectBundle\Entity\NotificationRecipient;
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Glavweb\RestBundle\Controller\GlavwebRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Symfony\Component\HttpFoundation\Response;
use Glavweb\CaptchaBundle\Validator\Constraints\IsTrue as CaptchaIsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ObjectApiController
 *
 * @package Glavweb\CompositeObjectBundle\Controller\Api
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ObjectApiController extends GlavwebRestController
{
    /**
     * Returns collection of objects
     *
     * @ApiDoc(
     *     views={"default", "composite-object"},
     *     section="Composite Object API",
     *     statusCodes={
     *         200="Returned when successful",
     *         206="Returned when successful",
     *         400="Returned when an error has occurred"
     *     }
     * )
     *
     * @Route("composite-objects/{class}", name="api_composite_objects_get_objects", defaults={"_format": "json"}, methods={"GET"})
     *
     * @ParamConverter("class", options={"mapping": {"class": "name"}})
     *
     * @Rest\QueryParam(name="filter", nullable=true, description="Filter")
     * @Rest\QueryParam(name="sort", nullable=true, description="Sort")
     * @Rest\QueryParam(name="limit", nullable=true, description="Limit")
     * @Rest\QueryParam(name="skip", nullable=true, description="Skip")
     * @Rest\QueryParam(name="projection", nullable=true, description="Projection")
     *
     * @param ObjectClass $class
     * @param ParamFetcherInterface $paramFetcher
     * @return ObjectInstance[]
     */
    public function getObjectsAction(ObjectClass $class, ParamFetcherInterface $paramFetcher)
    {
        $projection = (array)json_decode($paramFetcher->get('projection'), true);
        $limit      = $paramFetcher->get('limit');
        $skip       = $paramFetcher->get('skip');
        $sort       = (array)json_decode($paramFetcher->get('sort'), true);
        $filter     = (array)json_decode($paramFetcher->get('filter'), true);

        $className = $class->getName();

        if (!$class) {
            throw $this->createNotFoundException('Object class name not found.');
        }

        // Check allow method
        $this->checkAllowMethod($class, 'list');

        $mongodb    = $this->get('glavweb_mongodb');
        $collection = $mongodb->getDatabase()->$className;

        $data = $collection->find($filter, [
            'projection' => array_merge($projection, ['_id' => 0]), // hide mongodb ID
            'sort'       => $sort,
            'limit'      => $limit,
            'skip'       => $skip
        ])->toArray();

        return $data;
    }

    /**
     * Returns object
     *
     * @ApiDoc(
     *     views={"default", "composite-object"},
     *     section="Composite Object API",
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Returned when an error has occurred"
     *     }
     * )
     *
     * @Route("composite-objects/{class}/{id}", name="api_composite_objects_get_object", defaults={"_format": "json"}, methods={"GET"})
     *
     * @ParamConverter("class", options={"mapping": {"class": "name"}})
     *
     * @Rest\QueryParam(name="projection", nullable=true, description="Projection")
     *
     * @param ObjectClass $class
     * @param int $id
     * @param ParamFetcherInterface $paramFetcher
     * @return array
     */
    public function getObjectAction(ObjectClass $class, int $id, ParamFetcherInterface $paramFetcher)
    {
        $projection = (array)json_decode($paramFetcher->get('projection'), true);

        // Check allow method
        $this->checkAllowMethod($class, 'view');

        $mongodb    = $this->get('glavweb_mongodb');
        $className  = $class->getName();
        $collection = $mongodb->getDatabase()->$className;

        $data = $collection->find(['id' => $id], [
            'projection' => array_merge($projection, ['_id' => 0]) // hide mongodb ID
        ])->toArray();

        return $data;
    }

    /**
     * Create object instance
     *
     * @ApiDoc(
     *     views={"default", "composite-object"},
     *     section="Composite Object API",
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when an error has occurred",
     *     }
     * )
     *
     * @Route("composite-objects/{class}", name="api_composite_objects_create_object", defaults={"_format": "json"}, methods={"POST"})
     *
     * @ParamConverter("class", options={"mapping": {"class": "name"}})
     *
     * @param ObjectClass $class
     * @param Request $request A Symfony request
     * @return View|FormInterface
     */
    public function createObjectAction(ObjectClass $class, Request $request)
    {
        // Check allow method
        $this->checkAllowMethod($class, 'create');

        $form = $this->createObjectForm($class);

        $requestData = array_merge(
            $request->request->all(),
            $request->files->all()
        );
        $form->submit($requestData);

        if ($form->isValid()) {
            $data = $this->getFormData($form);

            // Save data
            $instance = new ObjectInstance();
            $instance->setClass($class);

            $objectManipulator = $this->get('glavweb_cms_composite_object.object_manipulator');
            $objectManipulator->saveObject($instance, $data);

            // Send notification message
            if ($class->getNotificationEnabled()) {
                $this->sendNotificationMessage($instance);
            }

            // Render result
            $view     = new View();
            $response = $view->getResponse();

            $response->setStatusCode(Response::HTTP_CREATED);
            $response->headers->set('Location',
                $this->generateUrl('api_composite_objects_get_object', [
                    'class' => $instance->getClass()->getName(),
                    'id'    => $instance->getId()
                ],true)
            );

            $view->setData($instance->getId());

            return $view;
        }

        return $form;
    }

    /**
     * Update object instance
     *
     * @ApiDoc(
     *     views={"default", "composite-object"},
     *     section="Composite Object API",
     *     statusCodes={
     *         200="Returned when successful",
     *         204="Returned when successful",
     *         400="Returned when an error has occurred",
     *     }
     * )
     *
     * @Route("composite-objects/{class}/{id}", name="api_composite_objects_put_object", defaults={"_format": "json"}, methods={"PUT"})
     * @Route("composite-objects/{class}/{id}", name="api_composite_objects_patch_object", defaults={"_format": "json", "isPatch": true}, methods={"PATCH"})
     *
     * @ParamConverter("class", options={"mapping": {"class": "name"}})
     * @ParamConverter("object", options={"mapping": {"id": "id"}})
     *
     * @param ObjectClass $class
     * @param ObjectInstance $object
     * @param Request $request
     * @param bool $isPatch
     * @return View|FormInterface
     */
    public function updateObjectInstanceAction(ObjectClass $class, ObjectInstance $object, Request $request, $isPatch = false)
    {
        if ($class->getName() != $object->getClass()->getName()) {
            throw $this->createNotFoundException();
        }

        // Check allow method
        $this->checkAllowMethod($class, 'update');

        $form = $this->createObjectForm($class);

        $requestData = array_merge(
            $request->request->all(),
            $request->files->all()
        );

        if ($isPatch) {
            $this->cleanForm($requestData, $form);
        }

        $form->submit($requestData);

        if ($form->isValid()) {
            $data = $this->getFormData($form);

            // Save data
            $objectManipulator = $this->get('glavweb_cms_composite_object.object_manipulator');
            $objectManipulator->saveObject($object, $data);

            // Render result
            $view     = new View();
            $response = $view->getResponse();
            $response->setStatusCode(Response::HTTP_OK);

            return $view;
        }

        return $form;
    }

    /**
     * Delete object instance
     *
     * @ApiDoc(
     *     views={"default", "composite-object"},
     *     section="Composite Object API",
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Returned when an error has occurred",
     *     }
     * )
     *
     * @Route("composite-objects/{class}/{id}", name="api_composite_objects_delete_object", defaults={"_format": "json"}, methods={"DELETE"})
     *
     * @ParamConverter("class", options={"mapping": {"class": "name"}})
     * @ParamConverter("object", options={"mapping": {"id": "id"}})
     *
     * @param ObjectClass $class
     * @param ObjectInstance $object
     * @return Response
     */
    public function deleteObjectInstanceAction(ObjectClass $class, ObjectInstance $object)
    {
        if ($class->getName() != $object->getClass()->getName()) {
            throw $this->createNotFoundException();
        }

        // Check allow method
        $this->checkAllowMethod($object->getClass(), 'delete');

        $objectManipulator = $this->get('glavweb_cms_composite_object.object_manipulator');
        $objectManipulator->deleteObject($object);

        return new Response('', 204);
    }

    /**
     * @param ObjectClass $class
     * @param string $testMethod
     */
    private function checkAllowMethod(ObjectClass $class, $testMethod = 'create')
    {
        if (!in_array($testMethod, $class->getApiMethods())) {
            throw $this->createNotFoundException('Method is not allowed for this class.');
        }
    }

    /**
     * @param Field $field
     * @return \Glavweb\CompositeObjectBundle\Provider\Field\FieldProviderInterface
     * @throws \Exception
     */
    private function getFieldProvider(Field $field)
    {
        $fieldProviderRegistry = $this->get('glavweb_cms_composite_object.field_provider_registry');
        $fieldProvider = $fieldProviderRegistry->get($field->getType());

        return $fieldProvider;
    }

    /**
     * @param ObjectClass $class
     * @return FormInterface
     */
    private function createObjectForm(ObjectClass $class)
    {
        /** @var FormBuilder $formBuilder */
        $formBuilder = $this->get('form.factory')->createNamedBuilder('', FormType::class, [], [
            'csrf_protection' => false,

        ]);

        foreach ($class->getFields() as $field) {
            $fieldProvider = $this->getFieldProvider($field);

            $formBuilder->add($fieldProvider->createFormBuilder($field, null, [
                'mapped' => false,
            ]));
        }

        if ($class->getCaptchaEnabled()) {
            $formBuilder->add('captcha_phrase', TextType::class, array(
                'label'       => false,
                'mapped'      => false,
                'constraints' => [
                    new CaptchaIsTrue()
                ]
            ));
            $formBuilder->add('captcha_token', HiddenType::class, array(
                'label'       => false,
                'mapped'      => false,
                'constraints' => [
                    new NotBlank()
                ]
            ));
        }

        $form = $formBuilder->getForm();

        return $form;
    }

    /**
     * Remove unnecessary fields from form
     *
     * @param array $requestData
     * @param FormInterface $form
     */
    private function cleanForm($requestData, FormInterface $form)
    {
        $allowedField = array_keys($requestData);

        /** @var FormInterface[] $formFields */
        $formFields = $form->all();
        foreach ($formFields as $formField) {
            $fieldName = $formField->getName();

            if (!in_array($fieldName, $allowedField)) {
                $form->remove($fieldName);
            }
        }
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    private function getFormData(FormInterface $form, $withoutSystemFields = true)
    {
        $systemFields = [
            'captcha_token',
            'captcha_phrase'
        ];

        $data = [];

        /** @var FormInterface[] $elements */
        $elements = $form->all();
        foreach ($elements as $element) {
            $fieldName = $element->getName();
            $valueData = $element->getData();

            if ($withoutSystemFields && in_array($fieldName, $systemFields)) {
                continue;
            }

            $data[$fieldName] = $valueData;
        }

        return $data;
    }

    /**
     * @param ObjectInstance $instance
     * @param string         $emailTemplate
     * @return mixed
     */
    private function sendNotificationMessage(ObjectInstance $instance, $emailTemplate = null)
    {
        /** @var EntityRepository $valueRepository */
        $em              = $this->get('doctrine')->getManager();
        $valueRepository = $em->getRepository(AbstractValue::class);
        $class           = $instance->getClass();
        $recipients      = $class->getRecipients();
        $emailTemplate   = $emailTemplate ?: 'GlavwebCompositeObjectBundle:email:email_body.html.twig';
        $emailFrom       = $this->getParameter('mailer_email_from');
        $emailName       = $this->getParameter('mailer_email_name');

        $values = $valueRepository->findBy([
            'instance' => $instance
        ], ['id' => 'ASC']);

        $recipientEmails = array_map(function ($recipient) {
            /** @var NotificationRecipient $recipient  */
            return $recipient->getEmail();
        }, $recipients->toArray());


        $body = $this->renderView($emailTemplate, [
            'instance' => $instance,
            'values'   => $values,
        ]);
        
        /** @var \Swift_Mime_Message $message */
        $mailer = $this->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($class->getLabel())
            ->setTo($recipientEmails)
            ->setBody($body, 'text/html')
        ;

        if ($emailFrom) {
            $message->setFrom($emailFrom, $emailName);
        }

        return $mailer->send($message);
    }
}
