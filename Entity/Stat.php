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

/**
 * Teapotio\Base\ForumBundle\Entity\Stat
 *
 * @ORM\MappedSuperclass
 */
class Stat implements StatInterface
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
     * @var integer $posts
     *
     * @ORM\Column(name="posts", type="integer")
     */
    protected $posts = 0;

    /**
     * @var integer $topics
     *
     * @ORM\Column(name="topics", type="integer")
     */
    protected $topics = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="boards", type="integer")
     */
    protected $boards = 0;

    /**
     * @var \DateTime $dateModified
     *
     * @ORM\Column(name="date_modified", type="datetime", nullable=true)
     */
    protected $dateModified;

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
     * Set posts
     *
     * @param integer $posts
     * @return Stat
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;

        return $this;
    }

    /**
     * Get posts
     *
     * @return integer
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Increase the total of posts in the topic
     *
     * @param  integer $int = 1
     *
     * @return Stat
     */
    public function increasePosts($int = 1)
    {
        $this->posts += $int;

        return $this;
    }

    /**
     * Decrease the total of posts in the topic
     *
     * @param  integer $int = 1
     *
     * @return  Stat
     */
    public function decreasePosts($int = 1)
    {
        $this->posts -= $int;

        return $this;
    }

    /**
     * Set topics
     *
     * @param integer $topics
     * @return Stat
     */
    public function setTopics($topics)
    {
        $this->topics = $topics;

        return $this;
    }

    /**
     * Get topics
     *
     * @return integer
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * Increase the total of topics in the topic
     *
     * @param  integer $int = 1
     *
     * @return Stat
     */
    public function increaseTopics($int = 1)
    {
        $this->topics += $int;

        return $this;
    }

    /**
     * Decrease the total of topics in the topic
     *
     * @param  integer $int = 1
     *
     * @return  Stat
     */
    public function decreaseTopics($int = 1)
    {
        $this->topics -= $int;

        return $this;
    }

    /**
     * Set boards
     *
     * @param integer $boards
     * @return Stat
     */
    public function setBoards($boards)
    {
        $this->boards = $boards;

        return $this;
    }

    /**
     * Get boards
     *
     * @return integer
     */
    public function getBoards()
    {
        return $this->boards;
    }

    /**
     * Increase the total of boards in the board
     *
     * @param  integer $int = 1
     *
     * @return Stat
     */
    public function increaseBoards($int = 1)
    {
        $this->boards += $int;

        return $this;
    }

    /**
     * Decrease the total of boards in the board
     *
     * @param  integer $int = 1
     *
     * @return  Stat
     */
    public function decreaseBoards($int = 1)
    {
        $this->boards -= $int;

        return $this;
    }

    /**
     * Set dateModified
     *
     * @param \DateTime $dateModified
     * @return Stat
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
     * Convenient methods
     */

    /**
     * Get a short value of the number of posts
     *
     * @return string
     */
    public function getShortPosts()
    {
        return $this->convertFormat($this->posts);
    }

    /**
     * Get a short value of the number of topics
     *
     * @return string
     */
    public function getShortTopics()
    {
        return $this->convertFormat($this->topics);
    }

    /**
     * Get a short value of the number of boards
     *
     * @return string
     */
    public function getShortBoards()
    {
        return $this->convertFormat($this->boards);
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
     * @return string
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