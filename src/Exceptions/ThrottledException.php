<?php
namespace Legalesign\Exceptions;

/**
 * Exception thrown when the Legalesign API quota has been exceeded, and requests are being throttled.
 *
 * The Legalesign API quota is set per-plan, and is a "soft" limit, so requests will not be immediately throttled if
 * the quota is exceeded, however Legalesign may manually choose to throttle the requests if the quota is regularly
 * exceeded. Whenever a request is throttled, this exception is thrown.
 *
 * @see \Exception
 * @package Legalesign\Exceptions
 * @author Tyler Menezes <tylermenezes@protonmail.ch>
 */
class ThrottledException extends \Exception {
    public function __construct()
    {
        parent::__construct('The Legalesign API account has exceeded its quota and is throttled.', 429);
    }
}
