<?php

namespace Bpi\Sdk\Tests\WebService;

class UserTest extends WebServiceTestCase
{
    public function testUsers()
    {
        $users = $this->searchUsers([]);
        $this->assertNotNull($users);
    }

    public function testCanCreateUser()
    {
        $users = $this->searchUsers();
        $numberOfUsers = count($users);

        $data = [
            'externalId' => uniqid('test'),
            'email' => uniqid('test') . '@example.com',
            'firstName' => uniqid('test'),
        ];

        $user = $this->client->createUser($data);

        $this->assertEquals($data['email'], $user->getEmail());
        $this->assertEquals($data['firstName'], $user->getFirstName());

        $users = $this->searchUsers();
        $newNumberOfUsers = count($users);

        $this->assertEquals($numberOfUsers + 1, $newNumberOfUsers);
    }

    // public function testCanCreateUser()
    // {
    //     $users = $this->searchUsers();
    //     $numberOfUsers = count($users);

    //     $user = $this->client->createUser([
    //         'externalId' => mt_rand(),
    //         'email' => mt_rand() . '@example.com',
    //     ]);

    //     $users = $this->searchUsers();
    //     $newNumberOfUsers = count($users);

    //     $this->assertEquals($numberOfUsers + 1, $newNumberOfUsers);
    // }

    public function testCanLimitUsers()
    {
        $this->client->createUser([ 'externalId' => mt_rand(), 'email' => mt_rand() . '@example.com' ]);
        $this->client->createUser([ 'externalId' => mt_rand(), 'email' => mt_rand() . '@example.com' ]);

        $users = $this->searchUsers([
        'amount' => 1,
        ]);

        $this->assertEquals(1, count($users));
    }

    public function testCanGetUser()
    {
        $newUser = $this->client->createUser([ 'externalId' => mt_rand(), 'email' => mt_rand() . '@example.com' ]);

        $user = $this->client->getUser($newUser->getId());

        $this->assertEquals($newUser, $user);
    }

    public function testCanSearchUsersByEmail()
    {
        $newUser = $this->client->createUser([ 'externalId' => mt_rand(), 'email' => mt_rand() . '@example.com', 'firstName' => mt_rand(), 'lastName' => mt_rand() ]);

        $users = $this->searchUsers([ 'search' => $newUser->getEmail() ]);

        $users->rewind();
        $this->assertEquals(1, count($users));
        $this->assertEquals($newUser, $users->current());
    }

    public function testCanUpdateUser()
    {
        $user = $this->client->createUser([ 'externalId' => mt_rand(), 'email' => mt_rand() . '@example.com' ]);

        $email = $user->getEmail() . mt_rand();
        $updatedUser = $this->client->updateUser($user->getId(), [ 'email' => $email ]);
        $this->assertEquals($email, $updatedUser->getEmail());

        $firstName = $user->getFirstName() . mt_rand();
        $updatedUser = $this->client->updateUser($user->getId(), [ 'firstName' => $firstName ]);
        $this->assertEquals($firstName, $updatedUser->getFirstName());
    }

    private function searchUsers(array $query = [])
    {
        $query += ['amount' => 1000];
        return $this->client->searchUsers($query);
    }
}
