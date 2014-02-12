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
use Teapotio\Base\ForumBundle\Entity\BoardStat;
use Teapotio\Base\ForumBundle\Entity\Topic;
use Teapotio\Base\ForumBundle\Entity\Message;

use Teapotio\Base\ForumBundle\Entity\BoardInterface;
use Teapotio\Base\ForumBundle\Entity\TopicInterface;
use Teapotio\Base\ForumBundle\Entity\MessageInterface;

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
        $useId = $this->container->getParameter('teapotio.forum.url.use_id');

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
            $parameters = array_merge($parameters, $this->container->get('teapotio.forum.path')->getTopicParameters($topic));
        }
        if ($board instanceof BoardInterface) {
            $parameters = array_merge($parameters, $this->container->get('teapotio.forum.path')->getBoardParameters($board));
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