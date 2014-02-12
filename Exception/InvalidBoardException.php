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

class InvalidBoardException extends \RuntimeException
{
    public function __construct($message = 'Board is invalid.', \Exception $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}