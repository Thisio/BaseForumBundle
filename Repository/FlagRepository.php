<?php

/**
 * Copyright (c) Thomas Potaire
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @category   Teapotio
 * @package    BaseForumBundle
 * @author     Thomas Potaire
 */

namespace Teapotio\Base\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Doctrine\Common\Collections\ArrayCollection;

class FlagRepository extends EntityRepository
{
    /**
     * Get the latest moderation actions
     *
     * @param  integer  $offset
     * @param  integer  $limit
     * @param  boolean  $isDeleted
     *
     * @return Paginator
     */
    public function getLatestFlags($offset, $limit, $isDeleted)
    {
        $queryBuilder = $this->createQueryBuilder('f')
                             ->select(array('f', 'mo', 'u', 'm', 't'))
                             ->leftJoin('f.moderation', 'mo')
                             ->leftJoin('f.users', 'u')
                             ->leftJoin('f.message', 'm')
                             ->leftJoin('f.topic', 't')
                             ->orderBy('f.id', 'DESC');

        if ($isDeleted !== null) {
            $queryBuilder->where('f.isDeleted = :isDeleted')
                         ->setParameter('isDeleted', $isDeleted);
        }

        $query = $queryBuilder->getQuery();

        $query->setFirstResult($offset)
              ->setMaxResults($limit);

        $paginator = new Paginator($query, false);
        return $paginator->setUseOutputWalkers(false);
    }


    public function getByMessages($messageIds, $isDeleted)
    {
        $queryBuilder = $this->createQueryBuilder('f')
                             ->select(array('f'));

        $queryBuilder->where($queryBuilder->expr()->in('f.message', $messageIds));

        if ($isDeleted !== null) {
            $queryBuilder->andWhere('f.isDeleted = :isDeleted')
                         ->setParameter('isDeleted', $isDeleted);
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
