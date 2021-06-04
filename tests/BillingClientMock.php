<?php

namespace App\Tests;

use App\Security\User;
use App\Service\BillingClient;


class BillingClientMock extends BillingClient
{

    public function login(string $login, string $password)
    {

        $user = new User();


        $user->setEmail($login);
        $user->setPassword($password);
        $user->setApiToken("");
        $roles[] = 'ROLE_USER';
        $user->setRoles($roles);

        return $user;
    }

    public function doSignup(string $email, string $password)
    {

        $user = new User();

        $user->setEmail($email);
        $user->setPassword($password);
        $user->setApiToken("kek");
        $roles[] = 'ROLE_USER';
        $user->setRoles($roles);
        return $user;
    }


}
