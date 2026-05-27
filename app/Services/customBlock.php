<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Category;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class customBlock
{

    /**
     * Retrieves data from the Cognifit API SDK.
     *
     * @param string $endPoint The endpoint to hit.
     * @param string $method The HTTP method to use.
     * @param string $locales The locales to use (default is "en").
     *
     * @return array The response data from the API.
     *
     * @throws \Throwable
     */
    public static function getSDKData($endPoint, $method, $locales = "en")
    {
        $array = [];

        try {
            $clientID = config('services.cognifit.client_id');

            $baseURL = config('services.cognifit.base_url') . '/' . $endPoint . '?client_id=' . $clientID;

            $new = new Client();

            $response = $new->request($method, $baseURL);

            $responseBody = json_decode($response->getBody()->getContents());

            $array = collect($responseBody);

            return $array;
        } catch (\Throwable $th) {
            return collect($array);
        }
    }


    /**
     * Retrieves data from a Firebase Realtime Database.
     *
     * @param string $path The path to the data in the Firebase Realtime Database.
     * @param \Illuminate\Support\Facades\FirebaseDatabase $database The Firebase Realtime Database instance.
     *
     * @return array The response data from the Firebase Realtime Database.
     *
     * @throws \Throwable
     */
    public static function getFireBaseData($path, $database)
    {

        $array = [];

        try {
            $list = $database->getReference($path)->getValue();

            $array = collect($list);
        } catch (\Throwable $th) {
            $array = collect([]);
        }

        return $array;
    }

    /**
     * Retrieves a specific item from a Firebase Realtime Database.
     *
     * @param string $path The path to the item in the Firebase Realtime Database.
     * @param \Illuminate\Support\Facades\FirebaseDatabase $database The Firebase Realtime Database instance.
     *
     * @return mixed The specific item from the Firebase Realtime Database, or null if the item does not exist.
     *
     * @throws \Throwable
     */
    public static function getSpecificData($path, $database)
    {
        $item = null;

        try {
            $list = $database->getReference($path)->getValue();

            $item = $list;
        } catch (\Throwable $th) {
            $item = null;
        }

        return $item;
    }

    /**
     * Prints a specific element from an array, or returns '-' if the element does not exist or is empty.
     *
     * @param array $data The array to retrieve the element from.
     * @param string $element The element to retrieve from the array.
     *
     * @return string The element from the array, or '-' if the element does not exist or is empty.
     */
    public static function printData($data, $element)
    {

        $printData = "-";

        // Check if the element exists and is not empty
        if (isset($data[$element]) && !empty($data[$element])) {

            $printData = $data[$element];

            return $printData;
        }

        // Return '-' if the element doesn't exist or is empty
        return $printData;
    }

    /**
     * Retrieves data from the Brain Games API.
     *
     * @param string $endPoint The endpoint to hit.
     * @param string $method The HTTP method to use.
     *
     * @return array The response data from the API.
     *
     * @throws \Throwable
     */
    public static function getBrainGamesData($endPoint, $method)
    {
        $array = [];

        try {
            $clientID = config('services.cognifit.client_id');
            $baseURL = config('services.cognifit.base_url') . '/' . $endPoint . '?client_id=' . $clientID . '&locales[]=en&locales[]=es';

            $client = new Client();
            $response = $client->request($method, $baseURL);

            $responseBody = json_decode($response->getBody()->getContents());

            $array = collect($responseBody);

            return $array;
        } catch (\Throwable $th) {
            return collect($array);
        }
    }

    /**
     * Replaces underscores with spaces in a given string.
     *
     * @param string $string The string to process.
     *
     * @return string The processed string.
     */
    public static function processStringNames(string $string)
    {
        $string = str_replace('_', ' ', $string);

        return $string;
    }


    /**
     * Generates a log entry in the database.
     *
     * @param string $path The path in the database to store the log.
     * @param array $log The log data to store.
     * @param \Illuminate\Database\DatabaseManager $dataBase The database manager instance to use.
     */
    public static function generateLogs($path, $log, $dataBase)
    {
        $dataBase->getReference($path)->push($log);
    }

    public static function createUser($name, $email, $password, $auth)
    {
        $create = $auth->createUser([
            'email' => $email,
            'emailVerified' => true,
            'password' => $password,
            'displayName' => $name,
            'disabled' => false,
        ]);

        $UID = $create->uid;

        return $UID;
    }

    public static function getUserBool($UID, $auth)
    {
        try {
            $user = $auth->getUser($UID);
            return true;
        } catch (UserNotFound $e) {
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function deleteCreatedUser($UID, $auth)
    {
        $auth->deleteUser($UID);
    }

    public static function deleteRecord($path, $database)
    {
        $database->getReference($path)->remove();
    }

}
