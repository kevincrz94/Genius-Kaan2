<?php

namespace App\Services;

use Carbon\Carbon;



class firebaseHelper
{

    public static function getData()
    {
        $length = intval(env('CODE_LENGTH'));

        $code = "";

        for ($i = 0; $i < $length; $i++) {
            $code .= rand(0, 9);
        }

        return $code;
    }

}
