<?php

namespace Neutron\Models;

use Neutron\Database\Model;

/**
 * User model representing the `users` table in the database.
 *
 * @property int    $id       The primary key of the user.
 * @property string $email    The email address of the user.
 * @property string $password The hashed password of the user.
 */
class User extends Model
{
    /**
     * The name of the database table associated with the User model.
     *
     * @var string
     */
    protected static string $table = 'users';

    /**
     * The primary key of the user.
     *
     * @var int
     */
    public int $id;

    /**
     * The email address of the user.
     *
     * @var string
     */
    public string $email;

    /**
     * The hashed password of the user.
     *
     * @var string
     */
    public string $password;

    /**
     * The role of the user.
     *
     * @var string
     */
    public string $role;
}
