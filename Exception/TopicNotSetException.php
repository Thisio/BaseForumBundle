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

namespace Teapot\Base\ForumBundle\Exception;

class TopicNotSetException extends \RuntimeException
{
    public function __construct($message = 'Topic is not set on your entity', \Exception $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}