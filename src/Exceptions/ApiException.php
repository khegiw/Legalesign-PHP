<?php
namespace Legalesign\Exceptions;

/**
 * General Legalesign API error.
 *
 * @see \Exception
 * @package Legalesign\Exceptions
 * @author Tyler Menezes <tylermenezes@protonmail.ch>
 */
class ApiException extends \Exception {
    public function __construct($status, $message = null)
    {
        if (!isset($message) || !trim($message)) {
            $message = $this->httpCodeToMessage($code);
        }

        parent::__construct('Legalesign API replied: '.$message, $status);
    }

    
    /**
     * Returns a reason for a request's failure from the HTTP status code.
     *
     * @param  int      $code   The HTTP status code
     * @return string?          The reason message for the failure, or NULL if unknown.
     */
    private function httpCodeToMessage($code)
    {
        switch ($status) {
            case 400:
                return 'Bad request; perhaps the POST request failed?';
            case 404:
                return 'That API endpoint does not exist.';
            case 405:
                return 'Method not allowed.';
            case 500:
                return 'Server error; perhaps you have malformed JSON or are missing a required property?';
            default:
                return null;
        }
    }
}
