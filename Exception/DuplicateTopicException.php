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

namespace Teapotio\Base\ForumBundle\Exception;

use Teapotio\Base\ForumBundle\Entity\TopicInterface;

class DuplicateTopicException extends \RuntimeException
{
    /**
     * @var  array  an array of existing topics with the same name or slug
     */
    public $topics;

    public function __construct(array $topics, $message = "This topic's name is already being used.", \Exception $previous = null)
    {
        $this->topics = $topics;

        parent::__construct($message, 500, $previous);
    }
}
