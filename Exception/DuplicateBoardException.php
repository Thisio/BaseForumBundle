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

use Teapotio\Base\ForumBundle\Entity\BoardInterface;

class DuplicateBoardException extends \RuntimeException
{
    /**
     * @var  BoardInterface  the existing board
     */
    public $board;

    public function __construct(BoardInterface $board, $message = "This board's name is already being used.", \Exception $previous = null)
    {
        $this->board = $board;

        parent::__construct($message, 500, $previous);
    }
}
