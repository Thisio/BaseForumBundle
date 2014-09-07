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

use Teapotio\Base\ForumBundle\Entity\BoardInterface;
use Teapotio\Base\ForumBundle\Entity\TopicInterface;

use Teapotio\Base\ForumBundle\Exception\DuplicateBoardException;
use Teapotio\Base\ForumBundle\Exception\DuplicateTopicException;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\ArrayCollection;

class PathService extends BaseService
{

    /**
     * Handle paths before a given board is saved
     *
     * @param  BoardInterface $board
     *
     * @return Board
     */
    public function onBoardEdit(BoardInterface $board)
    {
        return $this->handleBoardPath($board);
    }

    /**
     * Handle paths when a given board is created
     *
     * @param  BoardInterface $board
     *
     * @return Board
     */
    public function onBoardCreate(BoardInterface $board)
    {
        return $this->handleBoardPath($board);
    }

    /**
     * Returns the proper parameters of a board's url
     *
     * @param  BoardInterface $board
     *
     * @return array
     */
    public function getBoardParameters(BoardInterface $board)
    {
        $useId = $this->container->getParameter('teapotio.forum.url.use_id');

        $parameters = array(
            'boardSlug' => $this->container->get('teapotio.forum.board')->buildSlug($board)
        );

        if ($useId === true) {
            $parameters['boardId'] = $board->getId();
        }

        return $parameters;
    }

    /**
     * Lookup a board from the URL
     *
     * @return BoardInterface|null
     */
    public function lookupBoard()
    {
        $useId = $this->container->getParameter('teapotio.forum.url.use_id');

        // if the URL uses IDs
        if ($useId === true) {
            return $this->lookupBoardById();
        }
        else {
            return $this->lookupBoardBySlug();
        }
    }

    /**
     * Lookup a board by its ID in the URL
     *
     * @return BoardInterface|null
     */
    protected function lookupBoardById()
    {
        $boardId = $this->container->get('request')->attributes->get('boardId');

        return $board = $this->container->get('teapotio.forum.board')->getById($boardId);
    }

    /**
     * Lookup a board by its slug in the URL
     *
     * @return BoardInterface|null
     */
    protected function lookupBoardBySlug()
    {
        $boards = $this->container->get('teapotio.forum.board')->getBoards();

        // the whole slug in the URL (including the hierarchy)
        $wholeSlug = $this->container->get('request')->attributes->get('boardSlug');

        // split the whole slug into multiple pieces
        $slugParts = explode('/', $wholeSlug);

        return $this->searchBoardBySlug(0, $slugParts, $boards);
    }

    /**
     * This method is used when no ID are used in the URLs
     * Boards can be nested so we need to search through all boards until
     * we find the proper one
     *
     * @param  integer            $level
     * @param  array              $slugParts
     * @param  ArrayCollection    $boards
     *
     * @return BoardInterface|null
     */
    protected function searchBoardBySlug($level, $slugParts, $boards)
    {
        // If the number parts is inferior to the nesting level
        // then the URL is invalid
        if ($level >= count($slugParts)) {
            return null;
        }

        foreach ($boards as $board) {
            if ($board->getSlug() === $slugParts[$level]) {
                $level++;

                // If we reached the nesting level and
                // the slugs match then we got the right board
                if ($level === count($slugParts)) {
                    return $board;
                }

                return $this->searchBoardBySlug($level, $slugParts, $board->getChildren());
            }
        }

        return null;
    }

    /**
     * Handle generic related to a board's path
     * This method is shared between 'onBoardCreate' and 'onBoardEdit'
     *
     * @param  BoardInterface  $board
     *
     * @return Board
     *
     * @throws DuplicateBoardException
     */
    protected function handleBoardPath(BoardInterface $board)
    {
        /**
         * @todo  add a system to track previous URL so we can redirect the user to the proper path
         */
        $board->setSlug();

        if ($board->getParent() === null) {
            $boards = $this->container->get('teapotio.forum.board')->getBoards();
        } else {
            $boards = $board->getParent()->getChildren();
        }

        foreach ($boards as $b) {
            // If another board within the parent's children has the same slug - then we modify
            if ($b->getSlug() === $board->getSlug() && $b->getId() !== $board->getId()) {
                throw new DuplicateBoardException($b);
                break;
            }
        }

        return $board;
    }

    /**
     * Function used for board path deduping
     *
     * We decided to throw an exception instead so we can handle this scenario
     * properly (ie. show an error message and have the user fix the title)
     *
     * @param  BoardInterface  $board
     * @param  array           $boards
     *
     * @return Board
     */
    protected function dedupeBoardPath(BoardInterface $board, $boards)
    {
        $boardSlugs = array();

        // list all the parent's children slugs into one array
        foreach ($boards as $b) {
            $boardSlugs[] = $b->getSlug();
        }

        // append an hyphen to prepare for the rest of the logic
        // url will eventually look like 'this-is-a-test-12'
        $board->setSlug($board->getSlug() .'-');

        $i = 1;
        do {
            $slug = $board->getSlug() . $i;

            $i++;
        } while (in_array($slug, $boardSlugs));

        $board->setSlug($board->getSlug() . $i);

        return $board;
    }

    /**
     * Handle paths before a given topic is saved
     *
     * @param  TopicInterface $board
     *
     * @return Board
     */
    public function onTopicEdit(TopicInterface $topic)
    {
        return $this->handleTopicPath($topic);
    }

    /**
     * Handle paths when a given topic is created
     *
     * @param  TopicInterface $board
     *
     * @return Board
     */
    public function onTopicCreate(TopicInterface $topic)
    {
        return $this->handleTopicPath($topic);
    }

    /**
     * Check if a topic with the slug already exists
     *
     * @param  TopicInterface  $topic
     *
     * @return TopicInterface
     *
     * @throws DuplicateTopicException
     */
    protected function handleTopicPath(TopicInterface $topic)
    {
        // Get a list of topics by slug
        $topicsBySlug = $this->container
                             ->get('teapotio.forum.topic')
                             ->getTopicsBySlug($topic->getSlug());

        // Get a list of topics by slug
        $topicsByTitle = $this->container
                              ->get('teapotio.forum.topic')
                              ->getTopicsByTitle($topic->getTitle());

        $topics = array_merge($topicsBySlug, $topicsByTitle);

        // Remove the topic from the array if we are editing
        // the given topic
        foreach ($topics as $key => $t) {
            if ($t->getId() === $topic->getId()) {
                unset($topics[$key]);
            }
        }

        if (count($topics) !== 0) {
            throw new DuplicateTopicException($topics);
        }

        return $topic;
    }

    /**
     * Returns the proper parameters of a board's url
     *
     * @param  TopicInterface  $topic
     *
     * @return array
     */
    public function getTopicParameters(TopicInterface $topic)
    {
        $useId = $this->container->getParameter('teapotio.forum.url.use_id');

        $parameters = array(
            'topicSlug' => $topic->getSlug()
        );

        if ($useId === true) {
            $parameters['topicId'] = $topic->getId();
        }

        return $parameters;
    }
}
