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
        $newMessage = $this->httpCodeToMessage($status);
        if (isset($newMessage)) {
            $message = $newMessage;
        }
        if (!isset($message) || !trim($message)) {
            $message = $status;
        }

        parent::__construct('Legalesign API replied: '.$message, $status);
    }

    
    /**
     * Returns a reason for a request's failure from the HTTP status code.
     *
     * @param  int      $code   The HTTP status code
     * @return string?          The reason message for the failure, or NULL if unknown.
     */
    private function httpCodeToMessage($status)
    {
        switch ($status) {
            case 404:
                return 'Request failed. This usually indicates a required property was missing, the document with the'
                        .'ID requested does not exist, or you tried to use a template for a number of signers not'
                        .'matching the sent number.';
            case 405:
                return 'Method not allowed.';
            case 400:
            case 500:
                return 'Legalesign server error. (Perhaps the request was malformed.)';
            default:
                return null;
        }
    }
}
