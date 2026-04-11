<?php

namespace App\Services;

use App\Models\UserLogs;
use App\Models\userTransactions;
use Carbon\Carbon;
use App\Models\Users;
use App\Models\Listings;
use App\Models\Settings;



class userServices
{

    public static function createOTP()
    {
        $length = intval(env('CODE_LENGTH'));

        $code = "";

        for ($i = 0; $i < $length; $i++) {
            $code .= rand(0, 9);
        }

        return $code;
    }

    public static function verifyHeader($code, $dataBase)
    {
        $result = false;

        $getAuthHeader = $dataBase->getReference('authorization_code')->getValue();

        if ($getAuthHeader == $code) {
            $result = true;
        }

        $getPostmanAuthorization = $dataBase->getReference('postman_authorization_code')->getValue();

        if ($getPostmanAuthorization == $code) {
            $result = true;
        }

        return $result;
    }

    public static function createUserName($name)
    {
        $length = intval(env('CODE_LENGTH'));

        $code = "";

        for ($i = 0; $i < $length; $i++) {
            $code .= rand(0, 9);
        }


        $explodeName = explode(" ", $name);

        $firstName = $explodeName[0];

        $userName = $firstName . $code;

        return $userName;
    }

    public static function getFirstName($name)
    {
        $explodeName = explode(" ", $name);

        return $explodeName[0];
    }

    

}
