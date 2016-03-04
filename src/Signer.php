<?php
namespace Legalesign;

/**
 * Represents a document signer.
 *
 * @package Legalesign;
 * @author Tyler Menezes <tylermenezes@protonmail.ch>
 */
class Signer {
    public $id;
    public $firstName;
    public $lastName;
    public $email;
    public $behalfOf;

    public $message;
    public $extraMessage;

    public $status;
    public $order;

    /**
     *  Sends a reminder to sign to the user.
     *
     * @param string? $text     Optional, text to include in the reminder message.
     */
    public function remind($text = null)
    {
        $id = preg_replace("/[^A-Za-z0-9\- ]/", '', $this->id);
        Api::post('signer/'.$id.'/send-reminder/', ['text' => $text]);
    }

    
    /**
     *  Sets the status message to something helpful, based on the unhelpful status codes provided by legalesign.
     *
     * @param int $statusCode   The Legalesign status code
     */
    public function setStatusCode($statusCode)
    {
        if (!isset($statusCode)) return null;
        $codes = [
            5 => 'scheduled',
            10 => 'sent',
            15 => 'opened',
            20 => 'visited',
            30 => 'fields_completed',
            40 => 'signed',
            50 => 'downloaded'
        ];

        $this->status = $codes[$statusCode];
    }
}
