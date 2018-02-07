<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Admin;

use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CmsCoreBundle\Admin\AbstractAdmin;
use Glavweb\CmsCoreBundle\Admin\HasSortable;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AbstractObjectInstanceAdmin
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class AbstractObjectInstanceAdmin extends AbstractAdmin implements HasSortable
{
    /**
     * @var array
     */
    protected $datagridValues = array(
        '_page'       => 1,
        '_sort_order' => 'ASC',
        '_sort_by'    => 'position',
    );

    /**
     * @return array
     */
    public function getPersistentParameters()
    {
        return [
            'class' => $this->getObjectClassName()
        ];
    }

    /**
     * Override to orderby name and date
     *
     * @param string $context
     *
     * @return \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery
     */
    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = parent::createQuery($context);

        $aliases = $proxyQuery->getRootAliases();
        if (!isset($aliases[0])) {
            throw new \RuntimeException('No alias was set before invoking getRootAlias().');
        }

        $rootAlias = $aliases[0];

        $proxyQuery->andWhere(sprintf('%s.class = :class', $rootAlias));
        $proxyQuery->setParameter('class', $this->getObjectClass());

        return $proxyQuery;
    }

    /**
     * @return int
     */
    public function getObjectClassName()
    {
        $objectInstance = $this->getSubject();
        if ($objectInstance instanceof ObjectInstance && $objectInstance->getId()) {
            return $objectInstance->getClass()->getName();
        }

        $request = $this->getRequest();
        $className = $request->get('class');

        if (!$className) {
            throw new NotFoundHttpException('The class name must be defined.');
        }

        return $className;
    }

    /**
     * @return ObjectClass
     * @throws \Exception
     */
    public function getObjectClass()
    {
        $objectClassRepository = $this->getDoctrine()->getRepository(ObjectClass::class);

        $objectClass = $objectClassRepository->findOneBy(['name' => $this->getObjectClassName()]);

        if (!$objectClass) {
            throw new \Exception('Object class not found.');
        }

        return $objectClass;
    }
}
