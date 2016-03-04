<?php
namespace Legalesign;

/**
 * Represents a document signer.
 *
 * @package Legalesign;
 * @author Tyler Menezes <tylermenezes@protonmail.ch>
 */
class Signer {
    public $firstName;
    public $lastName;
    public $email;
    public $behalfOf;

    public $message;
    public $extraMessage;

    public $status;
    public $order;
    $signer->setStatusCode($signerInfo[6]);

    public setStatusCode($statusCode)
    {
        $codes = [
            5 => 'scheduled',
            10 => 'sent',
            15 => 'opened',
            20 => 'visited',
            30 => 'fields_completed',
            40 => 'signed',
            50 => 'downloaded'
        ];

        $this->status = $codes[$code];
    }
}
