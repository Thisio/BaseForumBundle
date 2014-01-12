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

namespace Teapot\Base\ForumBundle\Doctrine\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\NoResultException;

use Doctrine\ORM\Tools\Pagination\Paginator as BasePaginator;

class Paginator extends BasePaginator implements \Countable, \IteratorAggregate
{

    protected $iterated = null;

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if ($this->iterated !== null) {
            return $this->iterated;
        }

        $this->iterated = parent::getIterator();

        return $this->iterated;
    }
}