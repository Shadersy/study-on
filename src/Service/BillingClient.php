<?php

namespace App\Service;


use App\Repository\UserRepository;
use App\Security\User;

class BillingClient {

    protected function sendRequest(string $url, array $parameters) {
        $ch = curl_init();


        curl_setopt ($ch, CURLOPT_URL, 'http://billing.study-on.local/api/v1/auth');
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

    public function login(string $login, string $password) : ?User
    {

        $apiToken = json_decode($this->sendRequest('/api/v1/auth', ['username' => $login, 'password' => $password]), true);


        if (in_array('Invalid credentials.', $apiToken)) {
            return null;
        }

        $tokenParts = explode(".", $apiToken['token']);
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);

        $user = new User();
        //здесь как я полагаю нужно не выделять память под нового юзера а доставать через this->getUser (интерфейс)
        //просьба пока не обращать внимание на хардкод в константах


        $user->setRoles($jwtPayload->roles);
        $user->setApiToken($apiToken['token']);

        return $user;
    }
}