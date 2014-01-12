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

use Teapot\Base\ForumBundle\Entity\Board;
use Teapot\Base\ForumBundle\Entity\Topic;
use Teapot\Base\ForumBundle\Entity\Message;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Path extends \Twig_Extension {

    protected $container;

    public function __construct (ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            "forum_path" => new \Twig_Function_Method($this, 'forumPath')
        );
    }

    public function forumPath($routeName, $entity = null)
    {
        return $this->container->get('teapot.forum')->forumPath($routeName, $entity);
    }

    public function getName()
    {
        return "Path_Extension";
    }

}