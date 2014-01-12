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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Teapot\Base\ForumBundle\Entity\Message
 *
 * @ORM\MappedSuperclass
 */
class Message implements MessageInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;

    /**
     * @var string $body
     *
     * @ORM\Column(name="body", type="text")
     */
    protected $body;

    /**
     * @var integer $totalStarred
     *
     * @ORM\Column(name="total_starred", type="integer")
     */
    protected $totalStarred = 0;

    /**
     * @var \DateTime $dateCreated
     *
     * @ORM\Column(name="date_created", type="datetime")
     */
    protected $dateCreated;

    /**
     * @var \DateTime $dateModified
     *
     * @ORM\Column(name="date_modified", type="datetime", nullable=true)
     */
    protected $dateModified;

    /**
     * @var boolean $isDeleted
     *
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    protected $isDeleted = false;

    /**
     * @var boolean $isTopicBody
     *
     * @ORM\Column(name="is_topic_body", type="boolean")
     */
    protected $isTopicBody = false;

    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var \Teapot\Base\ForumBundle\Entity\Topic $topic
     */
    protected $topic;

    /**
     * @var ArrayCollection
     */
    protected $stars;

    public function __construct()
    {
        $this->stars = new ArrayCollection();
    }

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
     * Set position
     *
     * @param integer $position
     *
     * @return Message
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Message
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set total times the message was starred
     *
     * @param integer $totalStarred
     *
     * @return Message
     */
    public function setTotalStarred($int)
    {
        $this->totalStarred = $int;

        return $this;
    }

    /**
     * Get total times the message was starred
     *
     * @return integer
     */
    public function getTotalStarred()
    {
        return $this->totalStarred;
    }

    /**
     * Increase the total of times the message was starred
     *
     * @param  integer $int = 1
     *
     * @return Message
     */
    public function increaseTotalStarred($int = 1)
    {
        $this->totalStarred += $int;

        return $this;
    }

    /**
     * Decrease the total of times the message was starred
     *
     * @param  integer $int = 1
     *
     * @return  Message
     */
    public function decreaseTotalStarred($int = 1)
    {
        $this->totalStarred -= $int;

        return $this;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return Message
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
     * Set dateModified
     *
     * @param \DateTime $dateModified
     * @return Message
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * Get dateModified
     *
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return Message
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set isTopicBody
     *
     * @param  boolean $isTopicBody
     *
     * @return Message
     */
    public function setIsTopicBody($isTopicBody)
    {
        $this->isTopicBody = $isTopicBody;

        return $this;
    }

    /**
     * Get isTopicBody
     *
     * @return boolean
     */
    public function isTopicBody()
    {
        return $this->isTopicBody;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     * @return Message
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
     * Set topic
     *
     * @param \Teapot\Base\ForumBundle\Entity\Topic $topic
     * @return Message
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
     * Add MessageStar
     *
     * @param MessageStar $star
     *
     * @return Message
     */
    public function addStar(MessageStar $star)
    {
        $this->stars[] = $star;

        return $this;
    }

    /**
     * Set stars
     *
     * @return Message
     */
    public function setStars(ArrayCollection $stars)
    {
        $this->stars = $stars;

        return $this;
    }

    /**
     * Get a collection of stars
     *
     * @return ArrayCollection
     */
    public function getStars()
    {
        return $this->stars;
    }

    //
    // CONVENIENT METHODS
    //

    /**
     * Get this class' name
     *
     * @return string
     */
    public function getClassName()
    {
        return 'message';
    }
}
