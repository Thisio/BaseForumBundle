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

use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseService
{
    protected $container;
    protected $em;

    protected $boardRepositoryClass;
    protected $flagRepositoryClass;
    protected $topicRepositoryClass;
    protected $messageRepositoryClass;
    protected $messageStarRepositoryClass;
    protected $moderationRepositoryClass;
    protected $userStatRepositoryClass;

    public function __construct (ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();

        $this->boardRepositoryClass = $this->container->getParameter('teapotio_forum.board_repository.class');
        $this->flagRepositoryClass = $this->container->getParameter('teapotio_forum.flag_repository.class');
        $this->topicRepositoryClass = $this->container->getParameter('teapotio_forum.topic_repository.class');
        $this->messageRepositoryClass = $this->container->getParameter('teapotio_forum.message_repository.class');
        $this->messageStarRepositoryClass = $this->container->getParameter('teapotio_forum.message_star_repository.class');
        $this->moderationRepositoryClass = $this->container->getParameter('teapotio_forum.moderation_repository.class');
        $this->userStatRepositoryClass = $this->container->getParameter('teapotio_forum.user_stat_repository.class');
    }


}