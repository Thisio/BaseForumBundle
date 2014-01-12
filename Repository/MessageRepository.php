<?php

/**
 * Copyright (c) Thomas Potaire
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @category   Teapot
 * @package    BaseForumBundle
 * @author     Thomas Potaire
 */

namespace Teapot\Base\ForumBundle\Repository;

use Teapot\Base\ForumBundle\Entity\Message;
use Teapot\Base\ForumBundle\Entity\Topic;

use Teapot\Base\ForumBundle\Entity\MessageInterface;
use Teapot\Base\ForumBundle\Entity\TopicInterface;

use Doctrine\ORM\EntityRepository;
use Teapot\Base\ForumBundle\Doctrine\Pagination\Paginator;

class MessageRepository extends EntityRepository
{
    /**
     * Get a message by id
     *
     * @param  integer  $id
     * @param  boolean  $deleted
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
}