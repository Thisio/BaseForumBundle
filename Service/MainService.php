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

namespace Teapot\Base\ForumBundle\Service;

use Teapot\Base\ForumBundle\Entity\Board;
use Teapot\Base\ForumBundle\Entity\BoardStat;
use Teapot\Base\ForumBundle\Entity\Topic;
use Teapot\Base\ForumBundle\Entity\Message;

use Teapot\Base\ForumBundle\Entity\BoardInterface;
use Teapot\Base\ForumBundle\Entity\TopicInterface;
use Teapot\Base\ForumBundle\Entity\MessageInterface;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\ArrayCollection;

class MainService extends BaseService
{

    /**
     * Build a URL based on a given entity and a route
     *
     * @param  string  $routeName
     * @param  object  $entity
     *
     * @return string
     */
    public function forumPath($routeName, $entity = null)
    {
        $useId = $this->container->getParameter('teapot.forum.url.use_id');

        $parameters = array();
        $board = null;
        $topic = null;
        if ($entity instanceof BoardInterface) {
            $board = $entity;
        }
        if ($entity instanceof TopicInterface) {
            $board = $entity->getBoard();
            $topic = $entity;
        }
        if ($entity instanceof MessageInterface) {
            $board = $entity->getTopic()->getBoard();
            $topic = $entity->getTopic();
            $parameters['messageId'] = $entity->getId();
        }

        if ($topic instanceof TopicInterface) {
            $parameters = array_merge($parameters, $this->container->get('teapot.forum.path')->getTopicParameters($topic));
        }
        if ($board instanceof BoardInterface) {
            $parameters = array_merge($parameters, $this->container->get('teapot.forum.path')->getBoardParameters($board));
        }

        return $this->container->get('router')->generate($routeName, $parameters);
    }

    public function getTotalMessagesPerPage()
    {
        return 25;
    }

    public function getTotalTopicsPerPage()
    {
        return 40;
    }

}