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

namespace Teapotio\Base\ForumBundle\Extension;

use Teapotio\Base\ForumBundle\Entity\BoardInterface;
use Teapotio\Base\ForumBundle\Entity\TopicInterface;
use Teapotio\Base\ForumBundle\Entity\MessageInterface;

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
        return $this->container->get('teapotio.forum.board')->getBoards($deleted, $parentFirst);
    }

    public function getTopUsers($limit = 10)
    {
        return $this->container->get('teapotio.forum.user_stat')->getTopUsers($limit);
    }

    public function getLatestModerations($limit = 15)
    {
        return $this->container->get('teapotio.forum.moderation')->getLatestModerations(0, $limit);
    }

    public function getLastPage($totalMessages, $messagePerPage)
    {
        return ceil($totalMessages / $messagePerPage);
    }

    public function getLatestFlags($limit = 15)
    {
        return $this->container->get('teapotio.forum.flag')->getLatestFlags(0, $limit);
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
        return $this->container->get('teapotio.forum.message_star')->isMessageStarredByUser($message, $user);
    }

    public function getName()
    {
        return "Data_Access_Extension";
    }

}