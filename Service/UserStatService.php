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
use Teapotio\Base\ForumBundle\Entity\Topic;
use Teapotio\Base\ForumBundle\Entity\TopicStat;
use Teapotio\Base\ForumBundle\Entity\Message;
use Teapotio\Base\ForumBundle\Entity\UserStat;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\ArrayCollection;

class UserStatService extends BaseService
{
    public function createUserStat()
    {
        return new UserStat();
    }

    /**
     * Create UserStat entity
     *
     * @param  UserInterface $user
     * @param  boolean       $flush = true
     *
     * @return UserStat
     */
    public function createUserStatFromUser(UserInterface $user, $flush = true)
    {
        $userStat = $this->createUserStat();

        $userStat->setUser($user);

        $this->em->persist($userStat);

        if ($flush === true) {
            $this->em->flush();
        }

        return $userStat;
    }

    /**
     * Get a UserStat entity from a User and create one if the entity doesn't exist
     *
     * @param  UserInterface $user
     * @param  boolean       $flush = true
     *
     * @return UserStat
     */
    public function getByUserOrCreateOne(UserInterface $user, $flush = true)
    {
        // Get the user stat
        $userStat = $this->getByUser($user);

        // If null, we need to create a UserStat entity
        if ($userStat === null) {
            $userStat = $this->createUserStatFromUser($user, $flush);
        }

        return $userStat;
    }

    /**
     * Get a UserStat entity from a User
     *
     * @param  UserInterface $user
     *
     * @return UserStat|null
     */
    public function getByUser(UserInterface $user)
    {
        return $this->em
                    ->getRepository($this->userStatRepositoryClass)
                    ->getByUser($user);
    }

    /**
     * Get top users
     *
     * @param  integer $limit = 10
     *
     * @return ArrayCollection
     */
    public function getTopUsers($limit = 10)
    {
        return $this->em
                    ->getRepository($this->userStatRepositoryClass)
                    ->getTopUsers($limit);
    }
}