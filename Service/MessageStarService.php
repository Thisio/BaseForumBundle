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
use Teapotio\Base\ForumBundle\Entity\MessageStar;

use Teapotio\Base\ForumBundle\Entity\TopicInterface;
use Teapotio\Base\ForumBundle\Entity\MessageInterface;
use Teapotio\Base\ForumBundle\Entity\MessageStarInterface;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Doctrine\Common\Collections\ArrayCollection;

class MessageStarService extends BaseService
{

    /**
     * This is used for the template layer to avoid to many queries
     *
     * @var ArrayCollection
     */
    protected $userStarsByMessages = null;

    public function createMessageStar()
    {
        return new MessageStar();
    }

    /**
     * Save a starred message
     *
     * @param    MessageStarInterface    $star
     *
     * @return   MessageStarInterface
     */
    public function save(MessageStarInterface $star)
    {
        if ($star->getUser() === null) {
            throw new \RuntimeException('MessageStar entity must have a user associated to it.');
        }

        if ($star->getMessage() === null) {
            throw new \RuntimeException('MessageStar entity must have a message associated to it.');
        }

        $this->em->persist($star);
        $this->em->flush();

        return $star;
    }

    /**
     * Star a message
     *
     * @param  MessageInterface   $message
     * @param  UserInterface      $user
     *
     * @return boolen     whether the message starred or not
     */
    public function star(MessageInterface $message, UserInterface $user)
    {
        // whether the message was starred or not
        $changed = false;

        $star = $this->getByUserAndMessage($message, $user);

        if ($star === null) {
            $star = $this->createMessageStar();
            $star->setUser($user);
            $star->setMessage($message);
            $changed = true;
        }

        if ($star->isDeleted() === true) {
            $star->setIsDeleted(false);
            $changed = true;
        }

        if ($changed === true) {
            $message->increaseTotalStarred();
            $this->container->get('teapotio.forum.message')->save($message);

            $this->save($star);
        }

        return $changed;
    }

    /**
     * Unstar a message
     *
     * @param  MessageInterface $message
     * @param  UserInterface    $user
     *
     * @return boolen     whether the message unstarred or not
     */
    public function unstar(MessageInterface $message, UserInterface $user)
    {
        // whether the message was unstarred or not
        $changed = false;

        $star = $this->getByUserAndMessage($message, $user);

        if ($star !== null) {
            if ($star->isDeleted() === false) {
                $star->setIsDeleted(true);
                $changed = true;
            }

            if ($changed === true) {
                $message->decreaseTotalStarred();
                $this->container->get('teapotio.forum.message')->save($message);

                $this->save($star);
            }
        }

        return $changed;
    }

    /**
     * Return whether the message has been starred by the user or not
     *
     * @param  MessageInterface $message
     * @param  UserInterface    $user
     *
     * @return boolean
     */
    public function isMessageStarredByUser(MessageInterface $message, UserInterface $user)
    {
        // if the protected variable isn't null then it was initialized
        if ($this->userStarsByMessages !== null) {

            // if the message id exist in the collection and if the star hasn't been deleted
            if ($this->userStarsByMessages->containsKey($message->getId())
                && $this->userStarsByMessages->get($message->getId())->isDeleted() === false) {
                return true;
            }
            else {
                return false;
            }
        }

        $star = $this->getByUserAndMessage($message, $user);

        if ($star !== null && $star->isDeleted() === false) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Get a MessageStar based on a user and a message
     *
     * @param  MessageInterface   $message
     * @param  UserInterface      $user
     *
     * @return MessageStarInterface
     */
    public function getByUserAndMessage(MessageInterface $message, UserInterface $user)
    {
        return $this->em
                    ->getRepository($this->messageStarRepositoryClass)
                    ->findOneBy(array('user' => $user, 'message' => $message));
    }

    /**
     * Returns an array of array keyed by the message id
     *
     * @param  array  $messages   anything iterable
     *
     * @return array
     */
    public function getStarsByMessages($messages)
    {
        $return = array();
        $tmpMessages = array();

        foreach ($messages as $m) {
            if ($m->getTotalStarred() !== 0) {
                $tmpMessages[] = $m;
            }
            $return[$m->getId()] = new ArrayCollection();
        }

        // we query the DB only if there are messages with stars
        if (count($tmpMessages) !== 0) {
            $stars = $this->em
                          ->getRepository($this->messageStarRepositoryClass)
                          ->findBy(array('message' => $tmpMessages));

            foreach ($stars as $star) {
                $return[$star->getMessage()->getId()]->add($star);
            }
        }

        return $return;
    }

    /**
     * Returns null if the user is not logged-in otherwise returns a collection of stars
     *
     * @param  ArrayCollection|array  $messages
     * @param  UserInterface|null     $user
     *
     * @return ArrayCollection|null
     */
    public function getUserStarsByMessages($messages, $user = null)
    {
        // if we already processed this we return the original value
        if ($this->userStarsByMessages !== null) {
            return $this->userStarsByMessages;
        }

        // if the user isn't set we take the current user
        if ($user === null) {
            // if the user isn't logged in we return null
            if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') === false) {
                return null;
            }

            $user = $this->container->get('security.context')->getToken()->getUser();
        }

        $return = new ArrayCollection();
        $tmpMessages = array();

        foreach ($messages as $m) {
            if ($m->getTotalStarred() !== 0) {
                $tmpMessages[] = $m;
            }
        }

        // we query the DB only if there are messages with stars
        if (count($tmpMessages) !== 0) {
            $stars = $this->em
                          ->getRepository($this->messageStarRepositoryClass)
                          ->findBy(array('message' => $tmpMessages, 'user' => $user));

            foreach ($stars as $star) {
                $return->set($star->getMessage()->getId(), $star);
            }
        }

        $this->userStarsByMessages = $return;

        return $return;
    }

}