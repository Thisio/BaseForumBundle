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

namespace Teapot\Base\ForumBundle\Extension;

use Teapot\Base\ForumBundle\Entity\BoardInterface;
use Teapot\Base\ForumBundle\Entity\TopicInterface;
use Teapot\Base\ForumBundle\Entity\MessageInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DataAccess extends \Twig_Extension {

    protected $container;

    public function __construct (ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            "get_boards"                  => new \Twig_Function_Method($this, 'getBoards'),
            "get_top_forum_users"         => new \Twig_Function_Method($this, 'getTopUsers'),
            "get_latest_moderations"      => new \Twig_Function_Method($this, 'getLatestModerations'),
            "get_latest_flags"            => new \Twig_Function_Method($this, 'getLatestFlags'),
            "get_last_page"               => new \Twig_Function_Method($this, 'getLastPage'),
            "is_message_starred"          => new \Twig_Function_Method($this, 'isMessageStarred'),
            "is_message_starred_by_user"  => new \Twig_Function_Method($this, 'isMessageStarredByUser'),
        );
    }

    public function getBoards($deleted = false, $parentFirst = false)
    {
        return $this->container->get('teapot.forum.board')->getBoards($deleted, $parentFirst);
    }

    public function getTopUsers($limit = 10)
    {
        return $this->container->get('teapot.forum.user_stat')->getTopUsers($limit);
    }

    public function getLatestModerations($limit = 15)
    {
        return $this->container->get('teapot.forum.moderation')->getLatestModerations(0, $limit);
    }

    public function getLastPage($totalMessages, $messagePerPage)
    {
        return ceil($totalMessages / $messagePerPage);
    }

    public function getLatestFlags($limit = 15)
    {
        return $this->container->get('teapot.forum.flag')->getLatestFlags(0, $limit);
    }

    public function isMessageStarred($message)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') === false) {
            return false;
        }

        return $this->isMessageStarredByUser($message, $user);
    }

    public function isMessageStarredByUser($message, $user)
    {
        return $this->container->get('teapot.forum.message_star')->isMessageStarredByUser($message, $user);
    }

    public function getName()
    {
        return "Data_Access_Extension";
    }

}