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

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Teapot\Base\ForumBundle\Entity\Board
 *
 * @ORM\MappedSuperclass
 */
class Board implements BoardInterface
{

    const ACCESS_ACTION_VIEW   = 0;
    const ACCESS_ACTION_CREATE = 1;
    const ACCESS_ACTION_EDIT   = 2;
    const ACCESS_ACTION_DELETE = 3;

    const ACCESS_OBJECT_MESSAGE = 'msg';
    const ACCESS_OBJECT_TOPIC   = 'tpc';
    const ACCESS_OBJECT_BOARD   = 'brd';


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
     * @ORM\Column(name="title", type="string", length=64)
     */
    protected $title;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", length=64)
     */
    protected $slug;

    /**
     * @var string $shortTitle
     *
     * @ORM\Column(name="short_title", type="string", length=16)
     */
    protected $shortTitle;

    /**
     * @var string $body
     *
     * @ORM\Column(name="body", type="string", length=255, nullable=true)
     */
    protected $body;

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
     * @var string $permissions
     *
     * @ORM\Column(name="permissions", type="text")
     */
    protected $serializedPermissions;

    /**
     * Placeholder for unserialized data
     * If this variable is null; then it has not been initialized
     *
     *
     * @var array
     */
    protected $permissions = null;

    /**
     * @var boolean $isDeleted
     *
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    protected $isDeleted = false;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position = 1;

    /**
     * @var integer $depth
     *
     * @ORM\Column(name="depth", type="integer")
     */
    protected $depth = 1;

    /**
     * @var integer $posts
     *
     * @ORM\Column(name="total_posts", type="integer")
     */
    protected $totalPosts = 0;

    /**
     * @var integer $topics
     *
     * @ORM\Column(name="total_topics", type="integer")
     */
    protected $totalTopics = 0;

    /**
     * @var ArrayCollection $children
     */
    protected $children;

    /**
     * @var \Teapot\Base\ForumBundle\Entity\Board $parent
     */
    protected $parent;

    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var \Teapot\Base\ForumBundle\Entity\BoardStat $stat
     */
    protected $stat;

    /**
     * @var ArrayCollection $topics
     */
    protected $topics;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * A series of methods to be called to fully unserialize the class
     */
    public function unserialize()
    {
        $this->unserializePermissions();
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
     * @return Board
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
     * @return Board
     */
    public function setSlug($slug = null)
    {
        if ($slug === null) {
            // Found on Stackoverflow ~ originate from Symfony1 Jobeet
            // replace non letter or digits by -
            $slug = preg_replace('~[^\\pL\d]+~u', '-', $this->shortTitle);
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
     * Set shortTitle
     *
     * @param string $shortTitle
     * @return Board
     */
    public function setShortTitle($shortTitle)
    {
        $this->shortTitle = $shortTitle;

        return $this;
    }

    /**
     * Get shortTitle
     *
     * @return string
     */
    public function getShortTitle()
    {
        return $this->shortTitle;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Board
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
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return Board
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
     * @return Board
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
     * Set permissions
     *
     * @param array $permissions
     * @return Board
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        // serialize data unto the proper variable
        $this->serializePermissions();

        return $this;
    }

    /**
     * Get permissions
     *
     * @return array
     */
    public function getPermissions()
    {
        if ($this->permissions === null) {
            $this->unserializePermissions();
        }

        return $this->permissions;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     * @return Board
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Set position
     *
     * @param integer $int
     *
     * @return Board
     */
    public function setPosition($int)
    {
        $this->position = $int;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set depth
     *
     * @param integer $int
     *
     * @return Board
     */
    public function setDepth($int)
    {
        $this->depth = $int;

        return $this;
    }

    /**
     * Get total posts
     *
     * @return integer
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Set total posts
     *
     * @param integer $int
     *
     * @return Board
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
     * @return Board
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
     * @return  Board
     */
    public function decreaseTotalPosts($int = 1)
    {
        $this->totalPosts -= $int;

        return $this;
    }

    /**
     * Set totalTopics
     *
     * @param integer $int
     * @return BoardStat
     */
    public function setTotalTopics($int)
    {
        $this->totalTopics = $int;

        return $this;
    }

    /**
     * Get totalTopics
     *
     * @return integer
     */
    public function getTotalTopics()
    {
        return $this->totalTopics;
    }

    /**
     * Increase the total of topics in the topic
     *
     * @param  integer $int = 1
     *
     * @return  Board
     */
    public function increaseTotalTopics($int = 1)
    {
        $this->totalTopics += $int;

        return $this;
    }

    /**
     * Decrease the total of topics in the topic
     *
     * @param  integer $int = 1
     *
     * @return  Board
     */
    public function decreaseTotalTopics($int = 1)
    {
        $this->totalTopics -= $int;

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
     * Set user
     *
     * @param UserInterface $user
     * @return Board
     */
    public function setUser(UserInterface $user)
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
     * Set topics
     *
     * @return Board
     */
    public function setTopics(ArrayCollection $topics)
    {
        $this->topics = $topics;

        return $this;
    }

    /**
     * Get a collection of topics
     *
     * @return ArrayCollection
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * Add board child
     *
     * @param Board $board
     * @return Board
     */
    public function addChild(Board $board)
    {
        $this->children->add($board);

        return $this;
    }

    /**
     * Set children
     *
     * @return Board
     */
    public function setChildren(ArrayCollection $children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Get a collection of children
     *
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set board
     *
     * @param \Teapot\Base\ForumBundle\Entity\Board $parent
     * @return Board
     */
    public function setParent(\Teapot\Base\ForumBundle\Entity\Board $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get board
     *
     * @return \Teapot\Base\ForumBundle\Entity\Board
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get parent boards
     *
     * @return ArrayCollection
     */
    public function getParents()
    {
        $parents = array();

        $this->loadParent($this, $parents);

        return $parents;
    }

    /**
     * Serialize the permissions
     * We don't rely on doctrine events because we sometimes lazyload
     */
    public function serializePermissions()
    {
        $this->serializedPermissions = json_encode($this->permissions);
    }

    /**
     * Unserialize the permissions
     * We don't rely on doctrine events because we sometimes lazyload
     */
    public function unserializePermissions()
    {
        $this->permissions = json_decode($this->serializedPermissions, true);
    }

    /**
     * Load the parent and its predecessors
     */
    private function loadParent($board, &$parents)
    {
        if ($board->getParent() !== null) {
            $this->loadParent($board->getParent(), $parents);
            $parents[] = $board->getParent();
        }
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
        return 'board';
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
     * Get a short value of the number of topics
     *
     * @return string
     */
    public function getShortTotalTopics()
    {
        return $this->convertFormat($this->totalTopics);
    }

    /**
     * Can a user view messages directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserViewMessages(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_MESSAGE, self::ACCESS_ACTION_VIEW);
    }

    /**
     * Can a user view topics directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserViewTopics(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_TOPIC, self::ACCESS_ACTION_VIEW);
    }

    /**
     * Can a user view boards directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserViewBoards(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_BOARD, self::ACCESS_ACTION_VIEW);
    }

    /**
     * Can a user create messages directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserCreateMessages(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_MESSAGE, self::ACCESS_ACTION_CREATE);
    }

    /**
     * Can a user create topics directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserCreateTopics(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_TOPIC, self::ACCESS_ACTION_CREATE);
    }

    /**
     * Can a user create boards directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserCreateBoards(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_BOARD, self::ACCESS_ACTION_CREATE);
    }

    /**
     * Can a user edit messages directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserEditMessages(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_MESSAGE, self::ACCESS_ACTION_EDIT);
    }

    /**
     * Can a user edit topics directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserEditTopics(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_TOPIC, self::ACCESS_ACTION_EDIT);
    }

    /**
     * Can a user edit boards directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserEditBoards(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_BOARD, self::ACCESS_ACTION_EDIT);
    }

    /**
     * Can a user delete messages directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserDeleteMessages(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_MESSAGE, self::ACCESS_ACTION_DELETE);
    }

    /**
     * Can a user delete topics directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserDeleteTopics(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_TOPIC, self::ACCESS_ACTION_DELETE);
    }

    /**
     * Can a user delete boards directly within the board
     *
     * @param  UserInterface  $user
     *
     * @return boolean
     */
    public function canUserDeleteBoards(UserInterface $user = null)
    {
        return $this->hasUserAccess($user, self::ACCESS_OBJECT_BOARD, self::ACCESS_ACTION_DELETE);
    }

    /**
     * Returns whether a user has access (or not) to a Forum object within its children (boards, topics or messages)
     *
     * @param  UserInterface    $user
     * @param  string           $objectType    "board", "message" or "topic"
     * @param  integer          $permission    the values are defined above in constants
     *
     * @return boolean
     */
    protected function hasUserAccess(UserInterface $user = null, $objectType, $permission)
    {
        $hasAccess = false;

        if ($user === null) {
            return false;
        }

        foreach ($user->getGroups() as $group) {
            // loop through the groups until it says true otherwise return false at the end of the function
            if ($this->hasGroupAccess($group, $objectType, $permission) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Can a group view messages directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupViewMessages(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_MESSAGE, self::ACCESS_ACTION_VIEW);
    }

    /**
     * Can a group view topics directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupViewTopics(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_TOPIC, self::ACCESS_ACTION_VIEW);
    }

    /**
     * Can a group view boards directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupViewBoards(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_BOARD, self::ACCESS_ACTION_VIEW);
    }

    /**
     * Can a group create messages directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupCreateMessages(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_MESSAGE, self::ACCESS_ACTION_CREATE);
    }

    /**
     * Can a group create topics directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupCreateTopics(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_TOPIC, self::ACCESS_ACTION_CREATE);
    }

    /**
     * Can a group create boards directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupCreateBoards(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_BOARD, self::ACCESS_ACTION_CREATE);
    }

    /**
     * Can a group edit messages directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupEditMessages(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_MESSAGE, self::ACCESS_ACTION_EDIT);
    }

    /**
     * Can a group edit topics directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupEditTopics(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_TOPIC, self::ACCESS_ACTION_EDIT);
    }

    /**
     * Can a group edit boards directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupEditBoards(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_BOARD, self::ACCESS_ACTION_EDIT);
    }

    /**
     * Can a group delete messages directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupDeleteMessages(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_MESSAGE, self::ACCESS_ACTION_DELETE);
    }

    /**
     * Can a group delete topics directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupDeleteTopics(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_TOPIC, self::ACCESS_ACTION_DELETE);
    }

    /**
     * Can a group delete boards directly within the board
     *
     * @param  RoleInterface  $group
     *
     * @return boolean
     */
    public function canGroupDeleteBoards(RoleInterface $group = null)
    {
        return $this->hasGroupAccess($group, self::ACCESS_OBJECT_BOARD, self::ACCESS_ACTION_DELETE);
    }

    /**
     * Returns whether a group has access (or not) to a Forum object within its children (boards, topics or messages)
     *
     * @param  RoleInterface  $group
     * @param  string         $objecType    "board", "message" or "topic"
     * @param  integer        $permission   the values are defined above in constants
     *
     * @return boolean
     */
    protected function hasGroupAccess(RoleInterface $group, $objectType, $permission)
    {
        $permissions = $this->getPermissions();

        $objectType = strtolower($objectType);

        // if the group hasn't been defined in the permissions return false by default
        if (array_key_exists($group->getId(), $permissions) === false) {
            return false;
        }

        // if the object's permissions has not been defined we just deny access
        if (array_key_exists($objectType, $permissions[$group->getId()]) === false) {
            return false;
        }

        // If the permission isn't set return false
        if (array_key_exists($permission, $permissions[$group->getId()][$objectType]) === false) {
            return false;
        }

        if ($permissions[$group->getId()][$objectType][$permission] === 1) {
            return true;
        } else {
            return false;
        }
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
     *
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
