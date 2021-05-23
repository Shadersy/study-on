<?php

namespace App\Service;


use App\Repository\UserRepository;
use App\Security\User;


class BillingClient
{


    protected function sendRequest(string $url, array $parameters)
    {
        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_PORT, 82);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));

        $result = curl_exec($ch);
        curl_close($ch);


        return $result;
    }

    public function login(string $login, string $password)
    {

        $apiToken = json_decode($this->sendRequest('http://billing.study-on.local/api/v1/auth',
            ['username' => $login, 'password' => $password]), true);



        if($apiToken == null){
            return null;
        }

        if (in_array('Invalid credentials.', $apiToken)) {
            return $apiToken;
        }

        $tokenParts = explode(".", $apiToken['token']);
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);

        $user = new User();


        $user->setRoles($jwtPayload->roles);
        $user->setApiToken($apiToken['token']);

        return $user;
    }

    public function getBalanceToProfile(string $token)
    {

        $ch = curl_init();


        $requestHeader = "Authorization: Bearer " . $token;


        curl_setopt($ch, CURLOPT_URL, 'http://billing.study-on.local/api');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $requestHeader
        ));

        $balance = curl_exec($ch);

        curl_close($ch);


        return $balance;
    }


    public function doSignup(string $email, string $password)
    {

        $ch = curl_init();


        $apiToken = json_decode($this->sendRequest('http://billing.study-on.local/api/v1/register',
            ['email' => $email, 'password' => $password]), true);



        if (is_array($apiToken) && array_key_exists ( 'error' , $apiToken )) {
            return $apiToken;
        }

        if ($apiToken == null) {
            return null;
        }


        $tokenParts = explode(".", $apiToken['token']);
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);

        $user = new User();


        $user->setRoles($jwtPayload->roles);
        $user->setApiToken($apiToken['token']);


        return $user;
    }
}