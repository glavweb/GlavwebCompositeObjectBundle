<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Repository;

use Glavweb\CompositeObjectBundle\Entity\Field;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Glavweb\CompositeObjectBundle\Entity\Value\AbstractValue;
use Doctrine\ORM\EntityRepository;

/**
 * Class ValueRepository
 *
 * @package GlavwebCompositeObjectBundle
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class ValueRepository extends EntityRepository
{
    /**
     * @param Field $field
     * @param ObjectInstance $instance
     * @return AbstractValue|null
     */
    public function getValueByFieldAndInstance(Field $field, ObjectInstance $instance)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.field = :field AND t.instance = :instance')
            ->setParameter('field', $field)
            ->setParameter('instance', $instance)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}