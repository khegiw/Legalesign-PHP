<?php
namespace Legalesign\Exceptions;

/**
 * Exception thrown when a resource is theoretically available, but the Legalesign API does not have immediate access.
 *
 * @see \Exception
 * @package Legalesign\Exceptions
 * @author Tyler Menezes <tylermenezes@protonmail.ch>
 */
class RetrievingException extends \Exception {
    public function __construct()
    {
        parent::__construct('The requested resource is processing, and is not currently available. Try again in'
                            .'several hours.', 202);
    }
}
