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

use Teapotio\Base\ForumBundle\Entity\Message;
use Teapotio\Base\ForumBundle\Entity\Topic;

use Teapotio\Base\ForumBundle\Doctrine\Pagination\Paginator;

use Teapotio\Base\ForumBundle\Entity\MessageInterface;
use Teapotio\Base\ForumBundle\Entity\TopicInterface;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

class MessageRepository extends EntityRepository
{
    /**
     * Get a message by id
     *
     * @param  integer  $id
     * @param  boolean  $deleted = false
     *
     * @return MessageInterface
     */
    public function find($id, $isDeleted = false)
    {
        $queryBuilder = $this->createQueryBuilder('m')
                             ->select(array('m'))
                             // ->join('m.user', 'u')
                             ->where('m.id = :id')->setParameter('id', $id);

        if ($isDeleted !== null) {
            $queryBuilder->andWhere('m.isDeleted = :isDeleted')->setParameter('isDeleted', $isDeleted);
        }

        $query = $queryBuilder->getQuery();

        try {
            return $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            return null;
        }
    }

    /**
     * Get a collection of messages by ids
     *
     * @param  array    $ids
     * @param  boolean  $isDeleted = false
     *
     * @return ArrayCollection
     */
    public function getByIds($ids, $isDeleted = false)
    {
        $queryBuilder = $this->createQueryBuilder('m')
                             ->select(array('m'));

        $queryBuilder->where($queryBuilder->expr()->in('m.id', $ids));

        if ($isDeleted !== null) {
            $queryBuilder->andWhere('m.isDeleted = :isDeleted')->setParameter('isDeleted', $isDeleted);
        }

        $query = $queryBuilder->getQuery();

        return new ArrayCollection($query->getResult());
    }

    /**
     * Get a collection of messages
     *
     * @param  TopicInterface  $topic
     * @param  integer         $offset
     * @param  integer         $limit
     * @param  boolean         $isDeleted
     *
     * @return Paginator
     */
    public function getMessagesByTopic(TopicInterface $topic, $offset, $limit, $isDeleted)
    {
        $queryBuilder = $this->createQueryBuilder('m')
                             ->select(array('m'))
                             // ->join('m.user', 'u')
                             ->where('m.topic = :topic')->setParameter('topic', $topic);

        if ($isDeleted !== null) {
            $queryBuilder->andWhere('m.isDeleted = :isDeleted')->setParameter('isDeleted', $isDeleted);
        }

        $query = $queryBuilder->getQuery();

        $query->setFirstResult($offset)
              ->setMaxResults($limit);

        $paginator = new Paginator($query, false);
        return $paginator->setUseOutputWalkers(false);
    }

    /**
     * Get a topic body by topic
     *
     * @param  TopicInterface  $topic
     *
     * @return Message|null
     */
    public function getTopicBodyByTopic(TopicInterface $topic)
    {
        $queryBuilder = $this->createQueryBuilder('m')
                             ->select(array('m'))
                             // ->join('m.user', 'u')
                             ->where('m.topic = :topic')->setParameter('topic', $topic)
                             ->andWhere('m.isTopicBody = 1');

        $query = $queryBuilder->getQuery();

        try {
            return $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            return null;
        }
    }

    /**
     * Get topic bodies by topic ids
     *
     * @param  array  $topicIds
     *
     * @return array
     */
    public function getTopicBodiesByTopicIds(array $topicIds)
    {
        $queryBuilder = $this->createQueryBuilder('m')
                             ->select(array('m'))
                             ->andWhere('m.isTopicBody = 1');

        $queryBuilder->andWhere($queryBuilder->expr()->in('m.topic', $topicIds));

        $query = $queryBuilder->getQuery();

        return new ArrayCollection($query->getResult());
    }
}
