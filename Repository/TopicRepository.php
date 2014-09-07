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

use Teapotio\Base\ForumBundle\Entity\Board;
use Teapotio\Base\ForumBundle\Entity\Topic;
use Teapotio\Base\ForumBundle\Entity\Message;

use Teapotio\Base\ForumBundle\Entity\BoardInterface;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TopicRepository extends EntityRepository
{

    /**
     * Get all topics in board
     * Performance can be poor because it will gather all data
     *
     * @param  BoardInterface  $board
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getInBoard(BoardInterface $board)
    {
        $query = $this->createQueryBuilder('t')
                      ->select(array('t'))
                      ->where('t.board = :board')->setParameter('board', $board)
                      ->getQuery();

        $paginator = new Paginator($query, false);
        return $paginator->setUseOutputWalkers(false);
    }

    /**
     * Get the latest topics in general
     *
     * @param  integer  $offset
     * @param  integer  $limit
     * @param  boolean  $isDeleted
     * @param  array    $viewableBoardIds
     * @param  array    $restrictedBoardIds
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getLatestTopics($offset, $limit, $isDeleted, $viewableBoardIds, $restrictedBoardIds)
    {
        $queryBuilder = $this->createQueryBuilder('t')
                      ->select(array('t'))
                      ->orderBy('t.lastMessageDate', 'DESC');

        if ($isDeleted !== null) {
          $queryBuilder->where('t.isDeleted = :isDeleted')
                       ->setParameter('isDeleted', $isDeleted);
        }

        if (count($viewableBoardIds) > count($restrictedBoardIds) && count($restrictedBoardIds) !== 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('t.board', $restrictedBoardIds));
        } else if (count($restrictedBoardIds) !== 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('t.board', $viewableBoardIds));
        } else if (count($viewableBoardIds) === 0) {
            $queryBuilder->andWhere('t.board = 0'); // select topics from nowhere
        }

        $query = $queryBuilder->getQuery()
                              ->setFirstResult($offset)
                              ->setMaxResults($limit);

        $paginator = new Paginator($query, false);
        return $paginator->setUseOutputWalkers(false);
    }

    /**
     * Get the latest topics by board ids
     *
     * @param  array    $boardIds
     * @param  integer  $offset
     * @param  integer  $limit
     * @param  boolean  $isDeleted
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getLatestTopicsByBoardIds($boardIds, $offset, $limit, $isDeleted)
    {
        $queryBuilder = $this->createQueryBuilder('t')
                      ->select(array('t'))
                      ->orderBy('t.lastMessageDate', 'DESC');

        $queryBuilder->where($queryBuilder->expr()->in('t.board', $boardIds));

        if ($isDeleted !== null) {
          $queryBuilder->andWhere('t.isDeleted = :isDeleted')
                       ->setParameter('isDeleted', $isDeleted);
        }

        $query = $queryBuilder->getQuery()
                      ->setFirstResult($offset)
                      ->setMaxResults($limit);

        $paginator = new Paginator($query, false);
        return $paginator->setUseOutputWalkers(false);
    }

    /**
     * Get the latest topics by board
     *
     * @param  BoardInterface   $board
     * @param  integer          $offset
     * @param  integer          $limit
     * @param  boolean          $isDeleted
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getLatestTopicsByBoard(BoardInterface $board, $offset, $limit, $isDeleted)
    {
        $queryBuilder = $this->createQueryBuilder('t')
                             ->select(array('t'))
                             ->where('t.board = :board')->setParameter('board', $board)
                             ->orderBy('t.dateCreated', 'DESC');

        if ($isDeleted !== null) {
            $queryBuilder->andWhere('t.isDeleted = :isDeleted')
                         ->setParameter('isDeleted', $isDeleted);
        }

        $query = $queryBuilder->getQuery()
                              ->setFirstResult($offset)
                              ->setMaxResults($limit);

        $paginator = new Paginator($query, false);
        return $paginator->setUseOutputWalkers(false);
    }

    /**
     * Get the latest topics by user
     *
     * @param  UserInterface    $user
     * @param  integer          $offset
     * @param  integer          $limit
     * @param  array            $viewableBoardIds
     * @param  array            $restrictedBoardIds
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getLatestTopicsByUser(UserInterface $user, $offset, $limit, $viewableBoardIds, $restrictedBoardIds)
    {
        $queryBuilder = $this->createQueryBuilder('t')
                             ->select(array('t'))
                             ->where('t.user = :user')->setParameter('user', $user)
                             ->orderBy('t.dateCreated', 'DESC');

        if (count($viewableBoardIds) > count($restrictedBoardIds) && count($restrictedBoardIds) !== 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('t.board', $restrictedBoardIds));
        } else if (count($restrictedBoardIds) !== 0) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('t.board', $viewableBoardIds));
        } else if (count($viewableBoardIds) === 0) {
            $queryBuilder->andWhere('t.board = 0'); // select topics from nowhere
        }

        $query = $queryBuilder->getQuery()
                              ->setFirstResult($offset)
                              ->setMaxResults($limit);

        $paginator = new Paginator($query, false);
        return $paginator->setUseOutputWalkers(false);
    }

    /**
     * Move all topics from a board to another board
     *
     * @param  BoardInterface   $from
     * @param  BoardInterface   $to
     * @param  boolean          $flush = true
     *
     * @return integer
     */
    public function moveFromBoardToBoard(BoardInterface $from, BoardInterface $to)
    {
        return $this->createQueryBuilder('t')
                    ->update($this->getEntityName(), 't')
                    ->set('t.board', $to->getId())
                    ->where('t.board = :board')->setParameter('board', $from->getId())
                    ->getQuery()
                    ->execute();
    }

}
