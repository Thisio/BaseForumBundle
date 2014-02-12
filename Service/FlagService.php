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

namespace Teapotio\Base\ForumBundle\Service;

use Teapotio\Base\ForumBundle\Entity\Board;
use Teapotio\Base\ForumBundle\Entity\Topic;
use Teapotio\Base\ForumBundle\Entity\Message;
use Teapotio\Base\ForumBundle\Entity\Flag;
use Teapotio\Base\ForumBundle\Entity\Moderation;

use Teapotio\Base\ForumBundle\Entity\MessageInterface;
use Teapotio\Base\ForumBundle\Entity\TopicInterface;
use Teapotio\Base\ForumBundle\Entity\FlagInterface;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\ArrayCollection;

class FlagService extends BaseService
{

    public function createFlag()
    {
        return new Flag();
    }


    /**
     * Flag a topic or a message
     *
     * @param  Topic|Message   $entity
     * @param  UserInterface   $user
     *
     * @return boolean
     */
    public function flag($entity, UserInterface $user)
    {
        // we only accept a Topic entity or a Message entity
        if (false === $entity instanceof Topic && false === $entity instanceof Message) {
            throw new \InvalidArgumentException('Entity should be either a Topic entity or a Message entity.');
        }

        if ($entity->isDeleted() === true) {
            return false; // we don't bother flagging an item it already has been deleted
        }

        if (true === $entity instanceof Message) {
            $flag = $this->getByMessage($entity);
        }
        else if (true === $entity instanceof Topic) {
            $flag = $this->getByTopic($entity);
        }

        // if a flag for this entity doesn't exists, we initialize a new one
        if ($flag === null) {
            $flag = $this->createFlag();
            $flag->setDateCreated(new \DateTime());

            if (true === $entity instanceof Message) {
                $flag->setMessage($entity);
            } else if (true === $entity instanceof Topic) {
                $flag->setTopic($entity);
            }
        }
        // otherwise we check if the user already flagged this entity
        else {
            // if the flag already exists and has been deleted we silently validate anyways
            if ($flag->isDeleted() === true) {
                return true;
            }

            foreach ($flag->getUsers() as $u) {
                if ($u->getId() === $user->getId()) {
                    return true; // if the user already flagged the entity we silently validate anyways
                }
            }
        }

        $flag->addUser($user);
        $flag->setTotalFlagged($flag->getUsers()->count());

        $this->save($flag);

        return true;
    }

    /**
     * Ignore a flag and returns true
     *
     * @param  FlagInterface  $flag
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function ignore(FlagInterface $flag, UserInterface $user)
    {
        if ($flag->isDeleted() === true) {
            return true;
        }

        $flag->setIsDeleted(true);

        $this->save($flag);

        return true;
    }

    /**
     * Delete a flag, its flagged item and returns true
     *
     * @param  FlagInterface  $flag
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function delete(FlagInterface $flag, UserInterface $user)
    {
        if ($flag->getModeration() !== null) {
            return true;
        }

        $flag->setIsDeleted(true);

        $moderation = $this->container
                           ->get('teapotio.forum.moderation')
                           ->delete($flag->getFlaggedItem(), $user);

        $flag->setModeration($moderation);

        $this->save($flag);

        return true;
    }

    /**
     * Save a flag
     *
     * @param  FlagInterface   $flag
     *
     * @return FlagInterface
     */
    public function save(FlagInterface $flag)
    {
        $this->em->persist($flag);
        $this->em->flush();

        return $flag;
    }

    public function getById($id)
    {
        return $this->em
                    ->getRepository($this->flagRepositoryClass)
                    ->find($id);
    }

    /**
     * Get a flag by topic
     *
     * @param  TopicInterface  $topic
     *
     * @return FlagInterface|null
     */
    public function getByTopic(TopicInterface $topic)
    {
        return $this->em
                    ->getRepository($this->flagRepositoryClass)
                    ->findOneByTopic($topic);
    }

    /**
     * Get a flag by message
     *
     * @param  MessageInterface  $message
     *
     * @return FlagInterface|null
     */
    public function getByMessage(MessageInterface $message)
    {
        return $this->em
                    ->getRepository($this->flagRepositoryClass)
                    ->findOneByMessage($message);
    }

    /**
     * Get a collection of flags mapped by the message ids
     *
     * @param  Iterable         $messages
     * @param  BoardInterface   $board
     * @param  boolean          $isDeleted = false
     *
     * @return ArrayCollection
     */
    public function getByMessages($messages, $board, $isDeleted = false)
    {
        $flags = new ArrayCollection();

        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') === true) {
            $user = $this->container
                         ->get('security.context')
                         ->getToken()
                         ->getUser();

            if ($this->container->get('teapotio.forum.access_permission')->isModerator($user, $board) === false) {
                return $flags;
            }
        }

        $messageIds = array();
        foreach ($messages as $m) {
            $messageIds[] = $m->getId();
        }

        $tmp = $this->em
                    ->getRepository($this->flagRepositoryClass)
                    ->getByMessages($messageIds, $isDeleted);

        foreach ($tmp as $row) {
            $flags->set($row->getMessage()->getId(), $row);
        }

        return $flags;
    }

    /**
     * Get a collection of the latest flag
     *
     * @param  integer   $offset = 0
     * @param  integer   $limit = 15
     * @param  boolean   $isDeleted = false
     *
     * @return Paginator
     */
    public function getLatestFlags($offset = 0, $limit = 15, $isDeleted = false)
    {
        return $this->em
                    ->getRepository($this->flagRepositoryClass)
                    ->getLatestFlags($offset, $limit, $isDeleted);
    }

}