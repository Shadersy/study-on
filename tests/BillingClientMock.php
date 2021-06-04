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
        $user->setApiToken("eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MjI3OTE4NzIsImV4cCI6MTYyMjc5NTQ3Miwicm9sZXMiOlsiUk9MRV9TVVBFUl9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFkbWluQG1haWwucnUifQ.LKiDRPT6I6V_pq2y-zJQWEURknmPY4-bWsPYtwfRASIjHIRZ8hGhWmNr7y2fkbmyHqmf6w5o_OG8GQTTC8rlesLRersfpiwwc27OjWNRL0GkpbXh9jfi0bVosY_AmAD8YocG3UDiYxwhNMbHU_vN-JAgczKtwn7ZCtNVcPTrkRMH1s0qc8AwSnnd2DfeIG-cbZAdCtQygzg9Plym6zyOslQ-MAgrio3VNY1ijbabN4wBHvonOYViGr-VoXZ_KGdJ1rIp3uu6kCJxLV6_QwbrPRZvEDB7Nm5J5UBRKzT8LD36RnQHlFf3Pg6TMMyzRXeBq0gSGuwNaotoyB6h1aJQ9wprNZz3WbN_6wKBdaZO3c6byDakEfGPz_Nm53LPtOGWzbo-CxHP_hiAEB4tJ-mss56aXsTVU5Yxtn1FnUTzfLpwjrmwbw_TyXKAEFkfe5g441aPCGpVBdIm8HkriVZpCm_aaZZ-C11-OlJanMQQg7plnMQv0kzIJ1CySBgga0wirhMss88l_XDFa4V20wyo2dLpNRrfZmmKjTPkcRt_xOuyN5gR1V5W2anj-VLnZjSbwLLYrQ5d4c_3HmO0uJUhy4mm6jeOOHr8lXwGIy_ACt_j0fMpWQHoaiXgF-IF4GDl0oGirNYn0N-YFNN52E445TVgd_mgVUM28mIdoYqDgt4");
        $roles[] = 'ROLE_USER';
        $user->setRoles($roles);



        return $user;
    }

    public function doSignup(string $email, string $password)
    {

        $user = new User();

        $user->setEmail($email);
        $user->setPassword($password);
        $user->setApiToken("");
        $roles[] = 'ROLE_USER';
        $user->setRoles($roles);
        return $user;
    }


}
