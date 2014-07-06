<?php

/**
 * Copyright (c) Thomas Potaire
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @category   Teapotio
 * @package    BaseUserBundle
 */

namespace Teapotio\Base\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Role\RoleInterface;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * This class is used for anything related to user permissions in the forum system.
 * It should only be used within the boundaries of the ForumBundle and therefore
 * cannot be modified.
 * Do not assign this role to a logged-in user.
 *
 * @author Thomas Potaire
 */
final class AnonymousUserGroup implements RoleInterface
{
    /**
     * @var integer
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $name = 'Anonymous';

    /**
     * @var string
     */
    protected $role = 'ROLE_ANONYMOUS';

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
}
