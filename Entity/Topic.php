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

namespace Teapotio\Base\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Teapotio\Base\ForumBundle\Entity\Topic
 *
 * @ORM\MappedSuperclass
 */
class Topic implements TopicInterface
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
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=128)
     */
    protected $title;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=32)
     */
    protected $slug;

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
     * @var \DateTime $lastMessageDate
     *
     * @ORM\Column(name="last_message_date", type="datetime", nullable=true)
     */
    protected $lastMessageDate;

    /**
     * @var boolean $isDeleted
     *
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    protected $isDeleted = false;

    /**
     * @var boolean $isLocked
     *
     * @ORM\Column(name="is_locked", type="boolean")
     */
    protected $isLocked = false;

    /**
     * @var boolean $isPinned
     *
     * @ORM\Column(name="is_pinned", type="boolean")
     */
    protected $isPinned = false;

    /**
     * @var integer $totalViews
     *
     * @ORM\Column(name="total_views", type="integer")
     */
    protected $totalViews = 0;

    /**
     * @var integer $totalPosts
     *
     * @ORM\Column(name="total_posts", type="integer")
     */
    protected $totalPosts = 0;

    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var \Teapotio\Base\ForumBundle\Entity\TopicStat $stat
     */
    protected $stat;

    /**
     * @var \Teapotio\Base\ForumBundle\Entity\Board $board
     */
    protected $board;

    /**
     * @var \Teapotio\Base\ForumBundle\Entity\Message $body
     */
    protected $body = false;

    /**
     * @var ArrayCollection
     */
    protected $messages;

    /**
     * @var User $user
     */
    protected $lastUser;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Topic
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Topic
     */
    public function setSlug($slug = null)
    {
        if ($slug === null) {
            // Found on Stackoverflow ~ originate from Symfony1 Jobeet
            // replace non letter or digits by -
            $slug = preg_replace('~[^\\pL\d]+~u', '-', $this->title);
            // trim
            $slug = trim($slug, '-');
            // transliterate
            $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
            // lowercase
            $slug = strtolower($slug);
            // remove unwanted characters
            $slug = preg_replace('~[^-\w]+~', '', $slug);
        }

        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return Topic
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
     * @return Topic
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
     * Set lastMessageDate
     *
     * @param \DateTime $lastMessageDate
     * @return TopicStat
     */
    public function setLastMessageDate($lastMessageDate)
    {
        $this->lastMessageDate = $lastMessageDate;

        return $this;
    }

    /**
     * Get lastMessageDate
     *
     * @return \DateTime
     */
    public function getLastMessageDate()
    {
        return $this->lastMessageDate;
    }

    /**
     * Set total views
     *
     * @param integer $totalViews
     *
     * @return Topic
     */
    public function setTotalViews($totalViews)
    {
        $this->totalViews = $totalViews;

        return $this;
    }

    /**
     * Get total views
     *
     * @return integer
     */
    public function getTotalViews()
    {
        return $this->totalViews;
    }

    /**
     * Increase the total of views in the topic
     *
     * @param  integer $int = 1
     *
     * @return Topic
     */
    public function increaseTotalViews($int = 1)
    {
        $this->totalViews += $int;

        return $this;
    }

    /**
     * Decrease the total of views in the topic
     *
     * @param  integer $int = 1
     *
     * @return  Topic
     */
    public function decreaseTotalViews($int = 1)
    {
        $this->totalViews -= $int;

        return $this;
    }

    /**
     * Set total posts
     *
     * @param integer $totalPosts
     *
     * @return TopicStat
     */
    public function setTotalPosts($int)
    {
        $this->totalPosts = $int;

        return $this;
    }

    /**
     * Get total posts
     *
     * @return integer
     */
    public function getTotalPosts()
    {
        return $this->totalPosts;
    }

    /**
     * Increase the total of posts in the topic
     *
     * @param  integer $int = 1
     *
     * @return Topic
     */
    public function increaseTotalPosts($int = 1)
    {
        $this->totalPosts += $int;

        return $this;
    }

    /**
     * Decrease the total of posts in the topic
     *
     * @param  integer $int = 1
     *
     * @return  Topic
     */
    public function decreaseTotalPosts($int = 1)
    {
        $this->totalPosts -= $int;

        return $this;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return Topic
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
     * Set isLocked
     *
     * @param boolean $isLocked
     * @return Topic
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Get isLocked
     *
     * @return boolean
     */
    public function isLocked()
    {
        return $this->isLocked;
    }

    /**
     * Set isPinned
     *
     * @param boolean $isPinned
     * @return Topic
     */
    public function setIsPinned($isPinned)
    {
        $this->isPinned = $isPinned;

        return $this;
    }

    /**
     * Get isPinned
     *
     * @return boolean
     */
    public function isPinned()
    {
        return $this->isPinned;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     * @return Topic
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
     * Set board
     *
     * @param \Teapotio\Base\ForumBundle\Entity\Board $board
     * @return Topic
     */
    public function setBoard(\Teapotio\Base\ForumBundle\Entity\BoardInterface $board)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * Get board
     *
     * @return \Teapotio\Base\ForumBundle\Entity\BoardInterface
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set body
     *
     * @param \Teapotio\Base\ForumBundle\Entity\MessageInterface $board
     * @return Topic
     */
    public function setBody(\Teapotio\Base\ForumBundle\Entity\MessageInterface $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return \Teapotio\Base\ForumBundle\Entity\MessageInterface
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set messages
     *
     * @return Topic
     */
    public function setMessages(ArrayCollection $messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Get a collection of messages
     *
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     * @return TopicStat
     */
    public function setLastUser(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        $this->lastUser = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getLastUser()
    {
        return $this->lastUser;
    }

    /**
     * Convenient methods
     */

    /**
     * Get this class' name
     *
     * @return string
     */
    public function getClassName()
    {
        return 'topic';
    }

    /**
     * Get a short value of the number of posts
     *
     * @return string
     */
    public function getShortTotalPosts()
    {
        return $this->convertFormat($this->totalPosts);
    }

    /**
     * Get a short value of the number of views
     *
     * @return string
     */
    public function getShortTotalViews()
    {
        return $this->convertFormat($this->totalViews);
    }

    /**
     * Convert an integer into a short string
     *
     * 1000 -> 1K
     * 1500 -> 1.5K
     * 150500 -> 150K
     * 1000000 -> 1M
     * 1500000 -> 1.5M
     *
     * @param  integer  $var
     * @return integer
     */
    protected function convertFormat($var)
    {
        if ($var < 1000) {
            return $var;
        }
        else if ($var < 10000) {
            return round($var / 1000, 1) ."K";
        }
        else if ($var < 1000000) {
            return round($var / 1000) ."K";
        }
        else if ($var < 1000000) {
            return round($var / 1000000) . "M";
        }
    }
}
