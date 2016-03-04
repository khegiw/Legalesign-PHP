<?php
namespace Legalesign\Exceptions;

/**
 * Exception thrown when the Legalesign API rejects the userId/secret authentication pair.
 *
 * @see \Exception
 * @package Legalesign\Exceptions
 * @author Tyler Menezes <tylermenezes@protonmail.ch>
 */
class AuthenticationException extends \Exception {
    public function __construct()
    {
        parent::__construct('Failed to authenticate to the Legalesign API: the userId/secret pair was not valid.', 401);
    }
}
