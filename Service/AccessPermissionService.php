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
use Teapotio\Base\ForumBundle\Entity\TopicInterface;
use Teapotio\Base\ForumBundle\Entity\MessageInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\ArrayCollection;

class AccessPermissionService extends BaseService
{

    /**
     * Set all permissions on boards from the post data
     *
     * @param  integer   $groupId
     * @param  array     $data
     */
    public function setPermissionsOnBoardsFromPostData($groupId, $data)
    {
        $normalizedData = $this->translatePostDataInNormalizedData($groupId, $data);

        $this->setPermissionsOnBoardsFromNormalizedData($groupId, $normalizedData);
    }

    /**
     * Set all permissions on boards from normalized data
     *
     * @param  integer   $groupId
     * @param  array     $data
     */
    public function setPermissionsOnBoardsFromNormalizedData($groupId, $data)
    {
        $boards = $this->em->getRepository($this->boardRepositoryClass)->getBoards(null);

        // go through each board and its children recursively
        foreach ($boards as $board) {
            $this->setPermissionsOnBoardFromNormalizedData($board, $groupId, $data);
        }

        $this->em->flush();
    }

    /**
     * Set permissions on a specific board
     *
     * @param   BoardInterface   $board
     * @param   integer          $groupId
     * @param   array            $data
     *
     * @return  bool    returns whether permissions were changed or not
     */
    protected function setPermissionsOnBoardFromNormalizedData(BoardInterface $board, $groupId, $data)
    {
        $permissionChanged = false;

        $permissions = $board->getPermissions();

        // if there are no data for the given board we reset the permissions
        // if the permissions aren't empty empty we don't need to process anything
        if (array_key_exists($board->getId(), $data) === false
            && array_key_exists($groupId, $board->getPermissions()) === true
            && count($board->getPermissions()[$groupId]) !== 0) {
            $permissions[$groupId] = array();

            $permissionChanged = true;
        }
        // the permissions have been changed therefore
        else if (array_key_exists($board->getId(), $data) === true
                 && array_key_exists($groupId, $board->getPermissions()) === true
                 && json_encode($board->getPermissions()[$groupId]) !== json_encode($data[$board->getId()][$groupId])) {
            $permissions[$groupId] = $data[$board->getId()][$groupId];

            $permissionChanged = true;
        }
        // otherwise we are probably setting new permissions
        else if (array_key_exists($board->getId(), $data) === true
                 && array_key_exists($groupId, $board->getPermissions()) === false) {
            $permissions[$groupId] = $data[$board->getId()][$groupId];

            $permissionChanged = true;
        }

        if ($permissionChanged === true) {
            $this->em->refresh($board);

            $board->setPermissions($permissions);

            $this->em->persist($board);
        }

        return $permissionChanged;
    }

    /**
     * Returns normalized data from post data
     * This function is required to be run before anything go in the DB
     *
     * @param  integer   $groupId
     * @param  array     $data
     *
     * @return array
     */
    protected function translatePostDataInNormalizedData($groupId, $data)
    {
        $normalizedData = array();

        foreach ($data as $boardId => $permissions) {

            // If all checkboxes are unchecked
            if (count($permissions) === 1 && isset($permissions['decoy'])) {
                $normalizedData[$boardId][$groupId] = array();
                continue;
            } else {
                unset($permissions['decoy']);
            }

            // if the post data is set that means the user has view access across all type of objects
            $canView = isset($permissions['view']);
            unset($permissions['view']);
            // initialize the normalized permissions
            $normalizedData[$boardId] = array(
                $groupId => array()
            );

            // Set the "view" access permissions on the object types
            $normalizedData[$boardId][$groupId][Board::ACCESS_OBJECT_MESSAGE][Board::ACCESS_ACTION_VIEW] = (int)$canView;
            $normalizedData[$boardId][$groupId][Board::ACCESS_OBJECT_TOPIC][Board::ACCESS_ACTION_VIEW] = (int)$canView;
            $normalizedData[$boardId][$groupId][Board::ACCESS_OBJECT_BOARD][Board::ACCESS_ACTION_VIEW] = (int)$canView;

            // Loop through the different objects
            foreach ($permissions as $objectType => $actions) {
                switch ($objectType) {
                    case 'message':
                        $objectType = Board::ACCESS_OBJECT_MESSAGE;
                        break;
                    case 'topic':
                        $objectType = Board::ACCESS_OBJECT_TOPIC;
                        break;
                    case 'board':
                        $objectType = Board::ACCESS_OBJECT_BOARD;
                        break;
                    default:
                        throw new \RuntimeException(sprintf('An invalid objectType has been passed through the post data ("%s" given instead of message, topic or board).', $objectType));
                        break;
                }

                // Loop through the different object permissions
                foreach ($actions as $actionName => $str) {
                    switch ($actionName) {
                        case 'create':
                            $actionId = Board::ACCESS_ACTION_CREATE;
                            break;
                        case 'edit':
                            $actionId = Board::ACCESS_ACTION_EDIT;
                            break;
                        case 'delete':
                            $actionId = Board::ACCESS_ACTION_DELETE;
                            break;
                        case 'view':
                            $actionId = Board::ACCESS_ACTION_VIEW;
                            break;
                        default:
                            throw new \RuntimeException('An invalid actionName has been passed through the post data.');
                            break;
                    }

                    $normalizedData[$boardId][$groupId][$objectType][$actionId] = 1;
                }
            }
        }

        return $normalizedData;
    }

    /**
     * Returns whether a user is a super admin or not
     *
     * @param  UserInterface   $user = null
     *
     * @return boolean
     */
    public function isSuperAdmin(UserInterface $user = null)
    {
        return $this->container->get('teapotio.user')->isSuperAdmin($user);
    }

    /**
     * Returns whether a user ia an admin or not
     *
     * @param  UserInterface  $user = null
     *
     * @return boolean
     */
    public function isAdmin(UserInterface $user = null)
    {
        return $this->container->get('teapotio.user')->isAdmin($user);
    }

    /**
     * Returns whether a user is a moderator or not
     * If the board is null, it only checks if the user is in the moderator group
     *
     * @param  UserInterface    $user = null
     * @param  BoardInterface   $board = null
     *
     * @return boolean
     */
    public function isModerator(UserInterface $user = null, BoardInterface $board = null)
    {
        /**
         * @todo check against the board
         */

        // If the user is an admin or a super admin then he is automagically a moderator
        if ($this->isAdmin($user) === true || $this->isSuperAdmin($user) === true) {
            return true;
        }

        return false;
    }

    /**
     * Defines if a user can search topics, messages
     *
     * @param  UserInterface|null  $user
     *
     * @return boolean
     */
    public function canSearch(UserInterface $user = null)
    {
        // don't support search yet
        return false;

        $canSearch = false;

        // Super admin and admin can do all-the-things
        if ($this->isSuperAdmin($user) === true || $this->isAdmin($user) === true) {
            return true;
        }

        if ($this->container->get('teapotio.forum.board')->getViewableBoards($user)->count() !== 0) {
            $canSearch = true;
        }

        return $canSearch;
    }

    /**
     * Defines if a user can create a message
     * The board entity holds the logic that is directly related to its permissions
     *
     * @param  UserInterface|null    $user
     * @param  BoardInterface|null   $board
     *
     * @return boolean
     */
    public function canCreateMessage(UserInterface $user = null, BoardInterface $board = null)
    {
        // Super admin and admin can do all-the-things
        if ($this->isSuperAdmin($user) === true || $this->isAdmin($user) === true) {
            return true;
        }

        /**
         * @todo define the rule when a board isn't specified
         */
        if ($board === null) {
            return false;
        }

        return $board->canUserCreateMessages($user);
    }

    /**
     * Defines if a user can create a topic
     * The board entity holds the logic that is directly related to its permissions
     *
     * @param  UserInterface|null    $user
     * @param  BoardInterface|null   $board
     *
     * @return boolean
     */
    public function canCreateTopic(UserInterface $user = null, BoardInterface $board = null)
    {
        // Super admin and admin can do all-the-things
        if ($this->isSuperAdmin($user) === true || $this->isAdmin($user) === true) {
            return true;
        }

        /**
         * @todo define the rule when a board isn't specified
         */
        if ($board === null) {
            if ($user === null) {
                return false;
            } else {
                return true;
            }
        }

        return $board->canUserCreateTopics($user);
    }

    /**
     * Defines if a user can create a board
     * The board entity holds the logic that is directly related to its permissions
     *
     * @param  UserInterface|null    $user
     * @param  BoardInterface|null   $board
     *
     * @return boolean
     */
    public function canCreateBoard(UserInterface $user = null, BoardInterface $board = null)
    {
        // Super admin and admin can do all-the-things
        if ($this->isSuperAdmin($user) === true || $this->isAdmin($user) === true) {
            return true;
        }

        /**
         * @todo define the rule when a board isn't specified
         */
        if ($board === null) {
            return false;
        }

        return $board->canUserCreateBoards($user);
    }

    /**
     * Defines if a user can view a type of entity
     * The board entity holds the logic that is directly related to its permissions
     *
     * @param  UserInterface|null                               $user
     * @param  BoardInterface|TopicInterface|MessageInterface   $entity
     *
     * @return boolean
     */
    public function canView(UserInterface $user = null, $entity)
    {
        // Super admin and admin can do all-the-things
        if ($this->isSuperAdmin($user) === true || $this->isAdmin($user) === true) {
            return true;
        }

        if ($entity instanceof MessageInterface) {
            return $entity->getTopic()->getBoard()->canUserViewMessages($user);
        } else if ($entity instanceof TopicInterface) {
            return $entity->getBoard()->canUserViewTopics($user);
        } else if ($entity instanceof BoardInterface) {
            return $entity->canUserViewBoards($user);
        } else {
            throw new \RuntimeException("This entity type isn't supported.");
        }
    }

    /**
     * Defines if a user can edit a type of entity
     * The board entity holds the logic that is directly related to its permissions
     *
     * @param  UserInterface|null                               $user
     * @param  BoardInterface|TopicInterface|MessageInterface   $entity
     *
     * @return boolean
     */
    public function canEdit(UserInterface $user = null, $entity)
    {
        if ($user === null) {
            return false;
        }

        // Super admin and admin can do all-the-things
        if ($this->isSuperAdmin($user) === true || $this->isAdmin($user) === true) {
            return true;
        }

        /**
         * @todo this should be more complex
         */
        if ($entity->getUser() === null || $user->getId() !== $entity->getUser()->getId()) {
            return false;
        }

        if ($entity instanceof MessageInterface) {
            return $entity->getTopic()->getBoard()->canUserEditMessages($user);
        } else if ($entity instanceof TopicInterface) {
            return $entity->getBoard()->canUserEditTopics($user);
        } else if ($entity instanceof BoardInterface) {
            return $entity->canUserEditBoards($user);
        } else {
            throw new \RuntimeException("This entity type isn't supported.");
        }
    }

    /**
     * Defines if a user can delete a type of entity
     * The board entity holds the logic that is directly related to its permissions
     *
     * @param  UserInterface|null                               $user
     * @param  BoardInterface|TopicInterface|MessageInterface   $entity
     *
     * @return boolean
     */
    public function canDelete(UserInterface $user = null, $entity)
    {
        if ($user === null) {
            return false;
        }

        // Super admin and admin can do all-the-things
        if ($this->isSuperAdmin($user) === true || $this->isAdmin($user) === true) {
            return true;
        }

        /**
         * @todo this should be more complex
         */
        if ($user->getId() !== $entity->getUser()->getId()) {
            return false;
        }

        if ($entity instanceof MessageInterface) {
            return $entity->getTopic()->getBoard()->canUserDeleteMessages($user);
        } else if ($entity instanceof TopicInterface) {
            return $entity->getBoard()->canUserDeleteTopics($user);
        } else if ($entity instanceof BoardInterface) {
            return $entity->canUserDeleteBoards($user);
        } else {
            throw new \RuntimeException("This entity type isn't supported.");
        }
    }

    /**
     * Defines if a user can undelete a type of entity
     *
     * @param   UserInterface                                   $user = null
     * @param   MessageInterface|TopicInterface|BoardInterface  $entity
     *
     * @return  boolean
     */
    public function canUndelete(UserInterface $user = null, $entity)
    {
        if ($user === null) {
            return false;
        }

        return $this->isSuperAdmin($user) === true || $this->isAdmin($user) === true;
    }
}