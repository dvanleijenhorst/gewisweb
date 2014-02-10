<?php

namespace User\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * User model.
 *
 * @ORM\Entity
 */
class User implements RoleInterface, ResourceInterface
{

    /**
     * The membership number.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $lidnr;

    /**
     * The user's email address.
     *
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * The user's password.
     *
     * @ORM\Column(type="string")
     */
    protected $password;


    /**
     * Get the membership number.
     *
     * @return int
     */
    public function getLidnr()
    {
        return $this->lidnr;
    }

    /**
     * Get the user's email address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the password hash.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * Get the user's role ID.
     *
     * @return string
     */
    public function getRoleId()
    {
        return 'user_' . $this->getLidnr();
    }

    /**
     * Get the user's resource ID.
     *
     * @return string
     */
    public function getResourceId()
    {
        return 'user';
    }
}
