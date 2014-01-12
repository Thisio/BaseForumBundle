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

namespace Teapot\Base\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Teapot\Base\ForumBundle\Entity\Moderation
 *
 * @ORM\MappedSuperclass
 */
class Moderation implements ModerationInterface
{
    const ACTION_ID_LOCK = 10;
    const ACTION_ID_UNLOCK = 11;

    const ACTION_ID_PIN = 20;
    const ACTION_ID_UNPIN = 21;

    const ACTION_ID_DELETE = 30;
    const ACTION_ID_UNDELETE = 31;

    const ACTION_ID_MOVE = 90;

    protected $actionNames = array(
        10  => 'locked',
        11  => 'unlocked',
        20  => 'pinned',
        21  => 'unpinned',
        30  => 'deleted',
        31  => 'undeleted',
        90  => 'moved'
    );

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime $dateCreated
     *
     * @ORM\Column(name="date_created", type="datetime")
     */
    protected $dateCreated;

    /**
     * @var integer $actionId
     *
     * @ORM\Column(name="action_id", type="integer")
     */
    protected $actionId;

    /**
     * @var \Symfony\Component\Security\Core\User\UserInterface $user
     */
    protected $user;

    /**
     * @var \Teapot\Base\ForumBundle\Entity\Board $board
     */
    protected $board;

    /**
     * @var \Teapot\Base\ForumBundle\Entity\Topic $topic
     */
    protected $topic;

    /**
     * @var \Teapot\Base\ForumBundle\Entity\Message $message
     */
    protected $message;

    /**
     * @var \Teapot\Base\ForumBundle\Entity\Flag $flag
     */
    protected $flag;

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
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return Moderation
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set actionId
     *
     * @param  integer  $actionId
     *
     * @return Moderation
     */
    public function setActionId($actionId)
    {
        $this->actionId = $actionId;

        return $this;
    }

    /**
     * Get actionId
     *
     * @return \DateTime
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     * @return Moderation
     */
    public function setUser(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set moderator
     *
     * @param UserInterface $moderator
     * @return Moderation
     */
    public function setModerator(\Symfony\Component\Security\Core\User\UserInterface $moderator)
    {
        $this->moderator = $moderator;

        return $this;
    }

    /**
     * Get moderator
     *
     * @return UserInterface
     */
    public function getModerator()
    {
        return $this->moderator;
    }

    /**
     * Set message
     *
     * @param \Teapot\Base\ForumBundle\Entity\Message $message
     * @return Moderation
     */
    public function setMessage(\Teapot\Base\ForumBundle\Entity\Message $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \Teapot\Base\ForumBundle\Entity\Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set topic
     *
     * @param \Teapot\Base\ForumBundle\Entity\Topic $topic
     * @return Moderation
     */
    public function setTopic(\Teapot\Base\ForumBundle\Entity\Topic $topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return \Teapot\Base\ForumBundle\Entity\Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set board
     *
     * @param \Teapot\Base\ForumBundle\Entity\Board $board
     * @return Moderation
     */
    public function setBoard(\Teapot\Base\ForumBundle\Entity\Board $board)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * Get board
     *
     * @return \Teapot\Base\ForumBundle\Entity\Board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set flag
     *
     * @param \Teapot\Base\ForumBundle\Entity\Flag $flag
     * @return Moderation
     */
    public function setFlag(\Teapot\Base\ForumBundle\Entity\Flag $flag)
    {
        $this->flag = $flag;

        return $this;
    }

    /**
     * Get flag
     *
     * @return \Teapot\Base\ForumBundle\Entity\Flag
     */
    public function getFlag()
    {
        return $this->flag;
    }


    //
    // CONVINIENT METHODS
    //

    /**
     * Returns the action name related to this moderation
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionNames[$this->getActionId()];
    }

    /**
     * Returns the entity associated to this moderation
     *
     * @return Message|Topic|Board
     */
    public function getModeratedItem()
    {
        if ($this->getMessage() !== null) {
            return $this->getMessage();
        }
        else if ($this->getTopic() !== null) {
            return $this->getTopic();
        }
        else if ($this->getBoard() !== null) {
            return $this->getBoard();
        }
    }

    /**
     * Returns the name of the moderated item
     *
     * @return string
     */
    public function getModeratedItemName()
    {
        return $this->getModeratedItem()->getClassName();
    }

}