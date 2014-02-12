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

class AccessPermission extends \Twig_Extension {

    protected $container;

    public function __construct (ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            "can_create_message"    => new \Twig_Filter_Method($this, 'canCreateMessage'),
            "can_create_topic"      => new \Twig_Filter_Method($this, 'canCreateTopic'),
            "can_create_board"      => new \Twig_Filter_Method($this, 'canCreateBoard'),
            "can_view"              => new \Twig_Filter_Method($this, 'canView'),
            "can_edit"              => new \Twig_Filter_Method($this, 'canEdit'),
            "can_delete"            => new \Twig_Filter_Method($this, 'canDelete'),
            "can_search"            => new \Twig_Filter_Method($this, 'canSearch'),
            "is_super_admin"        => new \Twig_Filter_Method($this, 'isSuperAdmin'),
            "is_admin"              => new \Twig_Filter_Method($this, 'isAdmin'),
            "is_moderator"          => new \Twig_Filter_Method($this, 'isModerator'),
        );
    }

    public function canCreateMessage(UserInterface $user = null, BoardInterface $board = null)
    {
        return $this->container->get('teapotio.forum.access_permission')->canCreateMessage($user, $board);
    }

    public function canCreateTopic(UserInterface $user = null, BoardInterface $board = null)
    {
        return $this->container->get('teapotio.forum.access_permission')->canCreateTopic($user, $board);
    }

    public function canCreateBoard(UserInterface $user = null, BoardInterface $board = null)
    {
        return $this->container->get('teapotio.forum.access_permission')->canCreateBoard($user, $board);
    }

    public function canView(UserInterface $user = null, $entity)
    {
        return $this->container->get('teapotio.forum.access_permission')->canView($user, $entity);
    }

    public function canEdit(UserInterface $user = null, $entity)
    {
        return $this->container->get('teapotio.forum.access_permission')->canEdit($user, $entity);
    }

    public function canDelete(UserInterface $user = null, $entity)
    {
        return $this->container->get('teapotio.forum.access_permission')->canDelete($user, $entity);
    }

    public function canSearch(UserInterface $user = null)
    {
        return $this->container->get('teapotio.forum.access_permission')->canSearch($user);
    }

    public function isSuperAdmin(UserInterface $user = null)
    {
        return $this->container->get('teapotio.forum.access_permission')->isSuperAdmin($user);
    }

    public function isAdmin(UserInterface $user = null)
    {
        return $this->container->get('teapotio.forum.access_permission')->isAdmin($user);
    }

    public function isModerator(UserInterface $user = null)
    {
        return $this->container->get('teapotio.forum.access_permission')->isModerator($user);
    }

    public function getName()
    {
        return "Access_Permission_Extension";
    }
}