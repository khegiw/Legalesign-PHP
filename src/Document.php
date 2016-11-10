<?php
namespace Legalesign;

use Carbon\Carbon;

/**
 * Provides methods for creating, retrieving, updating, and deleting documents.
 *
 * @package  Legalesign
 * @author Tyler Menezes <tylermenezes@protonmail.ch>
 */
class Document {
    public $id;
    public $created_at;
    public $updated_at;
    public $signed_at;

    public $signers;
    public $status;

    public $downloadReady;
    public $downloadHash;


    protected function __construct($id)
    {
        $id = preg_replace("/[^A-Za-z0-9\- ]/", '', $id);
        $this->id = $id;

        $info = Api::get('document/'.$id.'/');
        $this->created_at = Carbon::parse($info->created);
        if (isset($info->updated)) {
            $this->updated_at = Carbon::parse($info->updated);
        } else {
            $this->updated_at = Carbon::parse($info->created);
        }
        $this->signed_at = isset($info->sign_time) ? Carbon::parse($info->sign_time) : null;

        $this->status = $this->statusCodeToStatus($info->status);
        $this->downloadReady = $info->download_final;
        $this->downloadHash = $info->hash_value;
        $this->signers = array_map(function($signerInfo) {
            $signer = new Signer;
            $signer->id = call_user_func(function($parts){ return $parts[count($parts) - 2]; },
                                          explode('/', $signerInfo[0]));
            $signer->firstName = $signerInfo[1];
            $signer->lastName = $signerInfo[2];
            $signer->email = $signerInfo[3];
            $signer->behalfOf = $signerInfo[4];
            $signer->setStatusCode($signerInfo[6]);
            $signer->order = $signerInfo[7];
            return $signer;
        }, $info->signers);
    }

    /**
     * Deletes the document.
     */
    public function delete()
    {
        Api::request('DELETE', 'document/'.$this->id.'/');
    }

    /**
     * Archives the document. 
     */
    public function archive()
    {
        Api::request('PATCH', 'document/'.$this->id.'/', [
            'archived' => true
        ]);
    }

    /**
     *  Gets the download URL.
     *
     *  @return string      The URL at which the PDF can be downloaded
     */
    public function getPdf()
    {
        if (!$this->downloadReady) return null;
        return Api::requestRaw('get', 'pdf/' . $this->id . '/')->getBody();
    }

    /**
     * Unarchives a document. 
     */
    public static function unarchive($id)
    {
        Api::request('PATCH', 'document/'.$id.'/', [
            'archived' => false
        ]);
    }

    // # Constructors

    /**
     * Loads an existing document.
     *
     * @param string    $id             The ID of the document.
     */
    public static function find($id)
    {
        $ret = new self($id);

        return $ret;
    }

    /**
     * Creates a new document. 
     *
     * @return Document\SigningRequest      Signing request generation object.
     */
    public static function create()
    {
        return new \Legalesign\Document\SigningRequest;
    }

    // # Internal
    /**
     *  Converts a Legalesign status code to a readable status message.
     *
     * @param   int     $code   Legalesign document status code
     * @return  string          Status message, one of: [sent, fields_complete, signed, cancelled]
     */
    protected function statusCodeToStatus($code)
    {
        if (!isset($code) || !is_numeric($code)) return null;
        $codes = [
            10 => 'sent',
            20 => 'fields_complete',
            30 => 'signed',
            40 => 'cancelled'
        ];
        return $codes[$code];
    }
}
