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

use Teapotio\Base\ForumBundle\Entity\BoardInterface;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\ArrayCollection;

class BoardService extends BaseService
{
    protected $boards = null;
    protected $viewableBoards = null;
    protected $restrictedBoards = null;

    public function createBoard()
    {
        return new Board();
    }

    /**
     * Returns a collection of boards the user has access to
     *
     * @return ArrayCollection
     */
    public function getViewableBoards()
    {
        // If it hasn't been loaded yet
        if ($this->viewableBoards === null) {
            $this->getBoards();
        }

        return $this->viewableBoards;
    }

    /**
     * Returns a collection of boards the user has not access to
     *
     * @return ArrayCollection
     */
    public function getRestrictedBoards()
    {
        // If it hasn't been loaded yet
        if ($this->restrictedBoards === null) {
            $this->getBoards();
        }

        return $this->restrictedBoards;
    }

    /**
     * Returns a multi-dimensional array
     * - first key is a list of viewable board ids
     * - second key is a list of restricted board ids
     *
     * @return array(array(viewableBoardIds), array(restrictedBoardIds))
     */
    public function getViewableAndRestrictedBoardIds()
    {
        $viewableBoards = $this->container
                               ->get('teapotio.forum.board')
                               ->getViewableBoards();

        $restrictedBoards = $this->container
                               ->get('teapotio.forum.board')
                               ->getRestrictedBoards();

        $viewableBoardIds = array();
        $restrictedBoardIds = array();

        $viewableBoards->forAll(function($key, $element) use (&$viewableBoardIds) {
            $viewableBoardIds[] = $element->getId();
            return true;
        });

        $restrictedBoards->forAll(function($key, $element) use (&$restrictedBoardIds) {
            $restrictedBoardIds[] = $element->getId();
            return true;
        });

        return array($viewableBoardIds, $restrictedBoardIds);
    }

    /**
     * Get a BoardInterface entity
     *
     * @param  integer  $boardId
     *
     * @return BoardInterface|null
     */
    public function getById($boardId)
    {
        return $this->em
                    ->getRepository($this->boardRepositoryClass)
                    ->find($boardId);
    }

    /**
     * Get a collection of boards by ids
     *
     * @param  array  $boardIds
     *
     * @return ArrayCollection
     */
    public function getByIds(array $boardIds)
    {
        $boards = $this->em
                       ->getRepository($this->boardRepositoryClass)
                       ->findById($boardIds);

        return new ArrayCollection($boards);
    }

    /**
     * Get a BoardInterface entity
     *
     * @param  slug  $boardSlug
     *
     * @return BoardInterface|null
     */
    public function getBySlug($boardSlug)
    {
        return $this->em
                    ->getRepository($this->boardRepositoryClass)
                    ->findOneBySlug($boardSlug);
    }

    /**
     * Get a collection of the main boards
     *
     * @return ArrayCollection
     */
    public function getMainBoards()
    {
        return $this->em
                    ->getRepository($this->boardRepositoryClass)
                    ->getMainBoards();
    }

    /**
     * Get a list of boards by parent board
     *
     * @param  BoardInterface  $board
     *
     * @return ArrayCollection
     */
    public function getBoardsByParentBoard(BoardInterface $board)
    {
        return $this->em
                    ->getRepository($this->boardRepositoryClass)
                    ->getBoardsByParentBoard($board);
    }

    /**
     * Get a list of boards by parent boards
     *
     * @param  ArrayCollection $boards
     *
     * @return ArrayCollection
     */
    public function getBoardsByParentBoards(ArrayCollection $boards)
    {
        return $this->em
                    ->getRepository($this->boardRepositoryClass)
                    ->getBoardsByParentBoards($boards);
    }

    /**
     * Gets all boards and build up the full hierarchie
     *
     * @param  boolean  $deleted = false
     * @param  boolean  $parentFirst = false    defines whether the list will be ordered by parent first then childless board (null will bypass that)
     *
     * @return ArrayCollection
     */
    public function getBoards($deleted = false, $parentFirst = false)
    {
        if ($this->boards !== null) {
            return $this->boards;
        }

        $user = null;
        $groupIds = array();
        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') === true) {
            $user = $this->container->get('security.context')->getToken()->getUser();
        }

        // Get boards ordered by depth and position so the parent of a board always comes first
        $boards = $this->em
                       ->getRepository($this->boardRepositoryClass)
                       ->getBoardsForTreeBuilding();

        $map = array();
        $mainBoards = new ArrayCollection();
        $this->viewableBoards = new ArrayCollection();
        $this->restrictedBoards = new ArrayCollection();

        foreach ($boards as $row) {
            $board = $row[0];
            $parentId = $row[1];

            if ($this->container->get('teapotio.forum.access_permission')->canView($user, $board) === false) {
                $this->restrictedBoards->add($board);
                continue;
            }

            $this->viewableBoards->add($board);

            // Entity is partially loaded so initialize the collection
            $board->setChildren(new ArrayCollection());

            $map[$board->getId()] = $board;

            // If parentId is null it means the board is at the root of the tree
            if ($parentId === null || array_key_exists($parentId, $map) === false) {
                $mainBoards->add($board);
            }
            else {
                $map[$parentId]->addChild($board);
                $board->setParent($map[$parentId]);
            }
        }

        $this->boards = $mainBoards;

        return $this->boards;
    }

    /**
     * @todo  review the usefulness of this function
     *
     * Reorder a board children (and recursively) by parent first or not
     *
     * @param  BoardInterface    $board
     * @param  boolean           $parentFirst
     *
     * @return Board
     */
    private function processOrderByChildless(BoardInterface $board, $parentFirst)
    {
        $childless = array();
        $parent = array();
        $children = array();

        // If the board has children then it's a parent and we process its children
        foreach ($board->getChildren() as $b) {
            if ($b->getChildren()->count() !== 0) {
                $b = $this->processOrderByChildless($b, $parentFirst);

                $parent[] = $b;
            }
            else {
                $childless[] = $b;
            }
        }

        // Apply order
        if ($parentFirst === true) {
            $children = array_merge($parent, $childless);
        }
        else {
            $children = array_merge($childless, $parent);
        }

        $board->setChildren(new ArrayCollection($children));

        return $board;
    }

    /**
     * Build an array of ids from all children of a board
     * and recursively get the children of the children
     *
     * @param  BoardInterface  $board
     * @param  UserInterface   $checkPermissions = null
     *
     * @return array
     */
    public function getChildrenIdsFromBoard(BoardInterface $board, UserInterface $user = null)
    {
        $ids = array();

        foreach ($board->getChildren() as $b) {
            if ($this->container->get('teapotio.forum.access_permission')->canView($user, $board) === true) {
              $ids[] = $b->getId();
            }

            if ($b->getChildren()->count() !== 0) {
                $ids = array_merge($ids, $this->getChildrenIdsFromBoard($b, $user));
            }
        }

        return $ids;
    }

    /**
     * Get the latest boards based on the board id
     *
     * @param  integer  $offset
     * @param  integer  $limit
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getLatestBoards($offset, $limit)
    {
        return $this->em
                    ->getRepository($this->boardRepositoryClass)
                    ->getLatestBoards($offset, $limit);
    }

    /**
     * Get the popular boards based on the number of posts
     *
     * @param  integer  $offset
     * @param  integer  $limit
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getPopularBoards($offset, $limit)
    {
        return $this->em
                    ->getRepository($this->boardRepositoryClass)
                    ->getPopularBoards($offset, $limit);
    }

    /**
     * Gets all boards
     * Careful! If you have thousands of boards, your server will run out of memory
     *
     * @return ArrayCollection
     */
    public function getAll()
    {
        return new ArrayCollection($this->em->getRepository($this->boardRepositoryClass)->findAll());
    }

    /**
     * Delete a board
     * Throws errors if the board is not clean
     *
     * @param  BoardInterface  $board
     *
     * @throws \Teapotio\Base\ForumBundle\Exception\TopicExistsException
     * @throws \Teapotio\Base\ForumBundle\Exception\BoardExistsException
     */
    public function delete(BoardInterface $board)
    {
        $boards = $this->getBoardsByParentBoard($board);

        if ($boards->count() !== 0) {
            throw new \Teapotio\Base\ForumBundle\Exception\BoardExistsException();
        }

        $topics = $this->container->get('teapotio.forum.topic')->getLatestTopicsByBoard($board, 0, 1);

        if ($topics->count() !== 0) {
            throw new \Teapotio\Base\ForumBundle\Exception\TopicExistsException();
        }

        $this->em->remove($board);
        $this->em->flush();
    }

    /**
     * Save a board and make sure all associations are built properly
     *
     * @param  BoardInterface  $board
     *
     * @return BoardInterface
     */
    public function save(BoardInterface $board)
    {
        // Pick a user if none was specified
        if ($board->getUser() === null) {
            $board->setUser($this->container->get('security.context')->getToken()->getUser());
        }

        // If the board is new
        if ($board->getId() === null) {
            $board->setDateCreated(new \DateTime());
        }
        else {
            $board->getDateModified(new \DateTime());
        }

        if ($board->getPermissions() === null) {
            $board->setPermissions(array());
        }

        $this->em->persist($board);

        $this->em->flush();

        return $board;
    }

    /**
     * Build a single slug for a board
     * It will go through the parent slug and attach them together
     *
     * @param  BoardInterface  $board
     *
     * @return string
     */
    public function buildSlug(BoardInterface $board)
    {
        $parents = $board->getParents();

        $boardSlug = "";
        foreach ($parents as $parent) {
            $boardSlug .= $parent->getSlug() ."-";
        }
        $boardSlug .= $board->getSlug();

        return $boardSlug;
    }

    /**
     * Change the parent board of all boards that has $original as a parent
     *
     * @param  BoardInterface  $original
     * @param  BoardInterface  $destination
     *
     * @return integer
     */
    public function switchBoardParent(BoardInterface $original, BoardInterface $destination)
    {
        return $this->em
                    ->getRepository($this->boardRepositoryClass)
                    ->switchBoardParent($original, $destination);
    }

    /**
     * Add a given number to the topics count of a given board
     * and bubbles up to its parents
     *
     * @param  BoardInterface   $board
     * @param  integer          $increment = 1
     *
     * @return BoardService
     */
    public function incrementStatTopics(BoardInterface $board, $increment = 1)
    {
        $board->increaseTotalTopics($increment);

        $this->save($board);

        if ($board->getParent() !== null) {
            $this->incrementStatTopics($board->getParent());
        }

        return $this;
    }

    /**
     * Add a given number to the posts count of a given board
     * and bubbles up to its parents
     *
     * @param  BoardInterface   $board
     * @param  integer          $increment = 1
     *
     * @return  BoardService
     */
    public function incrementStatPosts(BoardInterface $board, $increment = 1)
    {
        $board->increaseTotalPosts($increment);

        $this->save($board);

        if ($board->getParent() !== null) {
            $this->incrementStatPosts($board->getParent());
        }

        return $this;
    }

    public function moveBoard(BoardInterface $original, BoardInterface $destination)
    {

    }

    /**
     * Move the content of the original board into a destination board
     *
     * @param  BoardInterface  $original
     * @param  BoardInterface  $destination
     *
     * @throws \Teapotio\Base\ForumBundle\Exception\InvalidBoardException
     */
    public function moveContent(BoardInterface $original, BoardInterface $destination)
    {
        if ($original->getId() === $destination->getId()) {
            throw new \Teapotio\Base\ForumBundle\Exception\InvalidBoardException();
        }

        $posts = $original->getTotalPosts();
        $topics = $original->getTotalTopics();

        // Make sure the destination is not the children of the source
        $board = $destination->getParent();
        while ($board !== null) {
            if ($board->getId() === $original->getId()) {
                throw new \Teapotio\Base\ForumBundle\Exception\InvalidBoardException();
            }
            $board = $board->getParent();
        }

        $outOfScope = true;
        if ($original->getParent() !== null) {
           $outOfScope = $this->recurciveMoveNegation($original->getParent(), $destination, $posts, $topics);
        }

        if ($outOfScope === true) {
            $this->recurciveMoveAddition($destination, $posts, $topics);
        }

        // Move topics
        $this->container
             ->get('teapotio.forum.topic')
             ->moveFromBoardToBoard($original, $destination);

        // Move boards
        $this->switchBoardParent($original, $destination);

        $this->em->flush();
    }

    /**
     * Progress through the parents of the given boards and decrease their
     * stats with given post and topic counts.
     * If the destination board is reached it will stop recursive calls and return false
     * If the destionation board is not reached it will return true
     *
     * @param  BoardInterface   $source
     * @param  BoardInterface   $destination
     * @param  integer $posts
     * @param  integer $topics
     *
     * @return boolean
     */
    private function recurciveMoveNegation($source, $destination, $posts, $topics)
    {
        if ($source->getId() === $destination->getId()) {
            return false;
        }
        else {
            $source->decreaseTotalPosts($posts);

            $source->decreaseTotalTopics($topics);

            $this->save($source);
        }

        if ($source->getParent() !== null) {
            return $this->recurciveMoveNegation($source->getParent(), $destination, $posts, $topics);
        }
        else {
            return true;
        }
    }

    /**
     * Progress through the parents of the given boards and increase their
     * stats with given post and topic counts
     *
     * @param  BoardInterface   $board
     * @param  integer $posts
     * @param  integer $topics
     *
     * @return boolean
     */
    private function recurciveMoveAddition(BoardInterface $board, $posts, $topics)
    {
        $board->increaseTotalPosts($posts);

        $board->increaseTotalTopics($topics);

        $this->save($board);

        if ($board->getParent() !== null) {
            return $this->recurciveMoveAddition($board->getParent(), $posts, $topics);
        }
        else {
            return true;
        }
    }
}
