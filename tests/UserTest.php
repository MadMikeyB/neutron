<?php

use PHPUnit\Framework\TestCase;
use Neutron\Models\User;
use Neutron\Database\Connection;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        // Set up the in-memory SQLite database
        Connection::setPDO(new PDO('sqlite::memory:'));

        // Create the users table for testing
        $pdo = Connection::getPDO();
        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50)
            );
        ");
    }

    public function testInsertUser(): void
    {
        $user = new User();
        $user->email = 'foo@example.com';
        $user->password = password_hash('password', PASSWORD_BCRYPT);
        $user->save();

        $this->assertNotNull($user->id); // Check that the user was inserted and has an ID

        // Verify the user was inserted into the database
        $pdo = Connection::getPDO();
        $stmt = $pdo->query("SELECT * FROM users WHERE email = 'foo@example.com'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($result);
        $this->assertEquals('foo@example.com', $result['email']);
    }

    public function testUpdateUser(): void
    {
        // Insert user first
        $user = new User();
        $user->email = 'foo@example.com';
        $user->password = password_hash('password', PASSWORD_BCRYPT);
        $user->save();

        // Update the user's email
        $user->email = 'bar@example.com';
        $user->save();

        // Verify the update
        $pdo = Connection::getPDO();
        $stmt = $pdo->query("SELECT * FROM users WHERE email = 'bar@example.com'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('bar@example.com', $result['email']);
    }

    public function testDeleteUser(): void
    {
        // Insert user first
        $user = new User();
        $user->email = 'foo@example.com';
        $user->password = password_hash('password', PASSWORD_BCRYPT);
        $user->save();

        // Delete the user
        $user->delete();

        // Verify the deletion
        $pdo = Connection::getPDO();
        $stmt = $pdo->query("SELECT * FROM users WHERE email = 'foo@example.com'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse($result);
    }

    public function testWhereQuery(): void
    {
        // Insert multiple users
        $user1 = new User();
        $user1->email = 'user1@example.com';
        $user1->password = password_hash('password', PASSWORD_BCRYPT);
        $user1->role = 'admin';
        $user1->save();

        $user2 = new User();
        $user2->email = 'user2@example.com';
        $user2->password = password_hash('password', PASSWORD_BCRYPT);
        $user2->role = 'user';
        $user2->save();

        // Get users where role is 'admin'
        $admins = User::query()->where('role', '=', 'admin')->get();

        $this->assertCount(1, $admins);
        $this->assertEquals('user1@example.com', $admins[0]->email);
    }

    public function testLimitAndOffset(): void
    {
        // Insert multiple users
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->email = "user{$i}@example.com";
            $user->password = password_hash('password', PASSWORD_BCRYPT);
            $user->role = 'user';
            $user->save();
        }

        // Get only the second and third users
        $users = User::query()->limit(2)->offset(1)->get();

        $this->assertCount(2, $users);
        $this->assertEquals('user2@example.com', $users[0]->email);
        $this->assertEquals('user3@example.com', $users[1]->email);
    }

    public function testOrderBy(): void
    {
        // Insert multiple users
        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->email = "user{$i}@example.com";
            $user->password = password_hash('password', PASSWORD_BCRYPT);
            $user->role = 'user';
            $user->save();
        }

        // Get users ordered by email in descending order
        $users = User::query()->orderBy('email', 'DESC')->get();

        $this->assertCount(3, $users);
        $this->assertEquals('user3@example.com', $users[0]->email);
        $this->assertEquals('user1@example.com', $users[2]->email);
    }

    public function testExistsQuery(): void
    {
        // Insert a user
        $user = new User();
        $user->email = 'exists@example.com';
        $user->password = password_hash('password', PASSWORD_BCRYPT);
        $user->save();

        // Check if the user exists
        $exists = User::query()->where('email', '=', 'exists@example.com')->exists();

        $this->assertTrue($exists);
    }
}