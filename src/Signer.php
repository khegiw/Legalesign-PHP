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
     *  Gets the signing link.
     */
    public function getLink()
    {
        $response = Api::requestRaw('GET', 'signer/'.$this->id.'/new-link/');
        return $response->getHeader('Location')[0];
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

    /**
    * Gets the signer fields and values
    */
    public function getSignerFields()
    {
        $id = preg_replace("/[^A-Za-z0-9\- ]/", '', $this->id);
        $response = Api::get("signer/$id/fields1/");
        $fields = [];
        foreach ($response as $field) {
            $label = $field->label;
            if ($label) {
                $fields[$label] = $field->value;
            }
        }
        return $fields;
    }
}
