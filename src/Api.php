<?php
namespace Legalesign;

use GuzzleHttp\Client;

/**
 * Authenticates and submits API calls to Legalesign.
 *
 * The API class is responsible for storing the API credentials, as well as authenticating and submitting requests
 * to the Legalesign API.
 *
 * @package Legalesign
 * @author Tyler Menezes <tylermenezes@protonmail.ch>
 */
class Api {
    const ApiBase = 'https://legalesign.com/api/v1';

    protected static $api;
    protected static $userId;
    protected static $secret;

    /**
     * Sets the credentials for all API requests.
     *
     * Sets the credentials used for authenticating to the Legalesign API. You can get these credentials by going to
     * your personal Legalesign settings page (NOT the company settings page -- you'll need to click on the "Welcome
     * [YourName]" menu), and scrolling down to the bottom. You may need to have a paid plan to retrieve them.
     *
     * @param mixed $userId Your user ID (sometimes called an API Username, although not to be confused with an actual
     *                      username). Should be a 30-ish character string of hex digits.
     * @param mixed $secret Your API secret. Should be a 40-ish character string of hex digits.
     */
    public static function credentials($userId, $secret)
    {
        self::$userId = $userId;
        self::$secret = $secret;
    }

    /**
     * Makes a request to the Legalesign endpoint specified (using the specified HTTP method). Returns the raw Guzzle
     * Response object (after checking for errors and throwing exceptions as necessary).
     *
     * @param string                $method     The HTTP method to use for the request.
     * @param string                $endpoint   The API endpoint to request.
     * @param array?                $data       The data to send with the request, if any.
     * @return GuzzleHttp\Response              The response object, deserialized from response JSON.
     */
    public static function requestRaw($method, $endpoint, $data = [])
    {
        // Sanity checking and sanatizing
        if (!isset(self::$userId) || !self::$userId || !isset(self::$secret) || !self::$secret) {
            throw new \Exception('Legalesign credentials must be set prior to making a request.');
        }
        $method = trim(strtoupper($method));
        $endpoint = trim($endpoint);
        if (substr($endpoint, 0, 1) !== '/') {
            $endpoint = '/'.$endpoint;
        }

        // Check if the request is a GET request (in which case we should send the data as query string paramaters), or
        // any other type of request (in which case we should send the data as JSON in the request body).
        $dataLocation = 'json';
        if ($method === 'GET') {
            $dataLocation = 'query';
        }

        // Do the request
        $response = self::$api->request($method, $endpoint), [
            $dataLocation => $data,
            'headers' => [
                'Authorization' => 'ApiKey '.implode(':', [self::$userId, self::$secret]),
                'Content-Type' => 'application/json'
            ],
            'http_errors' => false
        ]);

        // Check the request's status, and throw exceptions as necessary.
        self::checkResponseForErrors($response);

        // Return the response.
        return $response;
    }

    /**
     * Makes a request to the Legalesign endpoint specified (using the specified HTTP method), deserializes the result
     * as JSON, and returns it.
     *
     * @param string    $method     The HTTP method to use for the request.
     * @param string    $endpoint   The API endpoint to request.
     * @param array?    $data       The data to send with the request, if any.
     * @return object               The response object, deserialized from response JSON.
     */
    public static function request($method, $endpoint, $data = [])
    {
        return json_decode(self::requestRaw($method, $endpoint, $data)->getBody());
    }

    /**
     *  Makes a GET request to the Legalesign API endpoint specified.
     *
     * @param string    $endpoint   The API endpoint to request.
     * @param array?    $data       The data to send with the request, if any.
     * @return object               The response object, deserialized from response JSON.
     */
    public static function get($endpoint, $data = []) {
        self::Request('GET', $endpoint, $data);
    }

    /**
     *  Makes a POST request to the Legalesign API endpoint specified.
     *
     * @param string    $endpoint   The API endpoint to request.
     * @param array?    $data       The data to send with the request, if any.
     * @return object               The response object, deserialized from response JSON.
     */
    public static function post($endpoint, $data = []) {
        self::Request('POST', $endpoint, $data);
    }

    // # Internal

    /**
     * Checks the API response to see whether it succeeded and, if not, throws the appropriate error.
     *
     * @param GuzzleHttp\Response   $response   The Legalesign API response.
     */
    private static function checkResponseForErrors(GuzzleHttp\Response $response)
    {
        switch ($response->getStatusCode()) {
            case 200:
            case 201:
            case 204:
                return;
            case 202:
                throw new Exceptions\RetrievingException();
            case 401:
                throw new Exceptions\AuthenticationException();
            case 429:
                throw new Exceptions\ThrottledException();
            default:
                throw new Exceptions\ApiException($response->getStatusCode(), $response->getBody());
        }
    }

    /**
     * Internal function for first-time setup of the class.
     *
     * Performs first-time setup for static properties of this class. Do not call this method, it will be automatically
     * called as soon as this file is loaded.
     */
    public static function boot()
    {
        // Check if this is Laravel; if so, automatically set the credentials from the configuration file.
        if (function_exists('config')) { // Laravel 5 and up
            self::credentials(config('legalesign.userid'), config('legalesign.secret'));
        } elseif (class_exists('\\Config')) { // Laravel 4.2 and below
            self::credentials(\Config::get('legalesign.userid'), \Config::get('legalesign.secret'));
        }

        // Set up the base Guzzle object to use for all requests. We won't hardcode the Authorization header here,
        // because the credentials will only be set for Laravel. We'll generate that header each time any request is
        // made.
        self::$legalesign = new Client([                                                                    
            'base_uri' => self::ApiBase
        ]);
    }
}
Api::boot();
