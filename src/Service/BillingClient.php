<?php

namespace App\Service;


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


        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));

        $result = curl_exec($ch);


        curl_close($ch);


        return $result;
    }

    public function login(string $login, string $password)
    {


        $apiToken = json_decode(
            $this->sendRequest(
                $_ENV["HOST_NAME_BILLING"] . '/api/v1/auth',
                ['username' => $login, 'password' => $password]), true);


        if ($apiToken == null) {
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
        $user->setRefreshToken($apiToken['refresh_token']);


        return $user;
    }

    public function getBalanceToProfile(string $token)
    {

        $ch = curl_init();


        $requestHeader = "Authorization: Bearer " . $token;


        curl_setopt($ch, CURLOPT_URL, $_ENV["HOST_NAME_BILLING"] . '/api/v1/current');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $requestHeader
        ));

        $balance = curl_exec($ch);

        curl_close($ch);


        return json_decode($balance)->balance;
    }

    public function getTransactions(string $token)
    {
        $ch = curl_init();


        $requestHeader = "Authorization: Bearer " . $token;

        curl_setopt($ch, CURLOPT_URL, $_ENV["HOST_NAME_BILLING"] . '/api/v1/transactions');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $requestHeader
        ));

        $transactions = curl_exec($ch);

        curl_close($ch);

        return $transactions;
    }


    public function doSignup(string $email, string $password)
    {

        $ch = curl_init();


        $apiToken = json_decode(
            $this->sendRequest(
                $_ENV["HOST_NAME_BILLING"] . '/api/v1/register',
            ['email' => $email, 'password' => $password]), true);


        if (is_array($apiToken) && array_key_exists('error', $apiToken)) {
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
        $user->setRefreshToken($apiToken["refreshToken"]);

        curl_close($ch);

        return $user;
    }


    public function getCourses(string $token)
    {
        $ch = curl_init();


        $requestHeader = "Authorization: Bearer " . $token;

        curl_setopt($ch, CURLOPT_URL, $_ENV["HOST_NAME_BILLING"] . '/api/v1/courses');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $requestHeader
        ));

        $courses = curl_exec($ch);

        curl_close($ch);


        return $courses;
    }


    public function payCourse(string $token, string $code)
    {
        $ch = curl_init();

        $requestHeader = "Authorization: Bearer " . $token;


        curl_setopt($ch, CURLOPT_URL, $_ENV["HOST_NAME_BILLING"] . '/api/v1/courses/' . $code . '/pay');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $requestHeader
        ));

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    public function checkAvailableCourse(string $token, string $code)
    {
        $ch = curl_init();

        $requestHeader = "Authorization: Bearer " . $token;


        curl_setopt($ch, CURLOPT_URL, $_ENV["HOST_NAME_BILLING"] .
            '/api/v1/transactions?filter[course]=' . $code .
            '&filter[skipexpired]=true&filter[type]=payment');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $requestHeader
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }


    public function createCourse(string $token, array $params)
    {

        $requestHeader = "Authorization: Bearer " . $token;


        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $_ENV["HOST_NAME_BILLING"] . '/api/v1/courses');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json', $requestHeader
        ));


        $response = json_decode(curl_exec($ch));
        curl_close($ch);


        return $response;
    }


    public function editCourse(string $token, array $params, string $currentCode)
    {

        $requestHeader = "Authorization: Bearer " . $token;


        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, $_ENV["HOST_NAME_BILLING"] . '/api/v1/courses/' . $currentCode);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json', $requestHeader
        ));


        $response = json_decode(curl_exec($ch));


        curl_close($ch);


        return $response;
    }

    public function refreshToken(string $expiredToken)
    {
        $response = json_decode($this->sendRequest(
            $_ENV["HOST_NAME_BILLING"] . '/api/v1/token/refresh',
            ['refresh_token' => $expiredToken]));

        return $response;
    }

    public function getPayload(string $token)
    {
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtPayload = json_decode($tokenPayload);

        return $jwtPayload;
    }
}
