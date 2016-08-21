<?php
namespace Legalesign\Document;

use Legalesign;

/**
 * Creates requests for document signings.
 *
 * This should generally only be instantiated from the Document::create() method.
 *
 * @package Legalesign\Document
 * @author Tyler Menezes <tylermenezes@protonmail.ch>
 */
class SigningRequest {
    protected $groupId;
    protected $name;
    protected $userId = null;

    // ## Options
    protected $appendLegalesignValidationInfo = true;
    protected $autoArchive = true;
    protected $sendNotificationEmails = true;
    protected $requestSignerLinks = false; // Not supported in this library yet.
    protected $signaturesOnAllPages = false;
    protected $certify = true;
    protected $signInOrder = true;

    // ## Password-protection
    protected $password = null;
    protected $storePassword = false;

    // ## Template info
    protected $footer = null;
    protected $footerHeight = null;
    protected $header = null;
    protected $headerHeight = null;

    // ## Configured through setters
    protected $signers = [];
    protected $cc = [];

    protected $_required = ['groupId', 'name', 'signers'];
    
    public function sendWithHtml($html)
    {
        return $this->doRequest(['text' => $html]);
    }

    public function sendWithTemplateHtml($template)
    {
        return $this->doRequest(['template' => '/api/v1/template/'.$template.'/']);
    }

    public function sendWithTemplatePdf($template)
    {
        return $this->doRequest(['templatepdf' => '/api/v1/templatepdf/'.$template.'/']);
    }

    // # Property setters

    public function group($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function user($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function appendLegalesignValidationInfo($appendLegalesignValidationInfo)
    {
        $this->appendLegalesignValidationInfo = $appendLegalesignValidationInfo;
        return $this;
    }

    public function autoArchive($autoArchive)
    {
        $this->autoArchive = $autoArchive;
        return $this;
    }

    public function signaturesOnAllPages($signaturesOnAllPages)
    {
        $this->signaturesOnAllPages = $signaturesOnAllPages;
        return $this;
    }

    public function certify($certify)
    {
        $this->certify = $certify;
        return $this;
    }

    public function signInOrder($signInOrder)
    {
        $this->signInOrder = $signInOrder;
        return $this;
    }

    public function requestSignerLinks($requestSignerLinks = true)
    {
        $this->requestSignerLinks = $requestSignerLinks;
        return $this;
    }

    /**
     *  Adds a signer to the signing request. If signInOrder is true, the signing order will be the same as the order
     *  in which the signers were added.
     *
     * @param Signer $signer    The signer to add to the signing request.
     */
    public function addSigner(Legalesign\Signer $signer)
    {
        $this->signers[] = $signer;
        return $this;
    }

    /**
     *  Adds an address which will be cc'd on all signing request-related communications.
     *
     * @param string $to The address to CC.
     */
    public function addCc($to)
    {
        $this->cc[] = $to;
        return $this;
    }

    // # Internal
    
    /**
     *  Runs the final request, and returns a Document object.
     *
     * @param   array       $with   Additional data to send to the API.
     * @return  Document            Created document for signing
     */
    protected function doRequest($with = [])
    {
        $data = array_merge($this->generateLegalesignRequestParams(), $with);
        $response = Legalesign\Api::requestRaw('POST', 'document/', $data);

        // Get the document's ID, which is in the URL Legalesign redirects to after a successful creation.
        $apiEntityUrl = $response->getHeader('Location')[0];
        $id = call_user_func(function($parts){ return $parts[count($parts) - 2]; }, explode('/', $apiEntityUrl));

        return Legalesign\Document::find($id, json_decode($response->getBody()));
    }

    /**
     * Ensures all required fields are populated. Returns true if so, or throws an exception if not. 
     */
    protected function checkRequired()
    {
        foreach ($this->_required as $required) {
            if (!isset($this->$required) || !$this->$required)
                throw new \InvalidArgumentException("$required is required.");

            if (   (is_string($this->$required) && strlen(trim($this->$required)) === 0)
                || (is_array($this->$required) && count($this->$required) === 0))
                    throw new \InvalidArgumentException("$required must not be empty.");
        }

        return true;
    }

    /**
     * Converts configuration from this object into Legalesign's API format. 
     *
     * @return array        Object containing Legalesign configuration data.
     */
    protected function generateLegalesignRequestParams()
    {
        $this->checkRequired();

        $request = [
            'group' => '/api/v1/group/'.$this->groupId.'/',
            'name' => $this->name,
            'append_pdf' => $this->appendLegalesignValidationInfo,
            'auto_archive' => $this->autoArchive,
            'do_email' => $this->sendNotificationEmails,
            'footer' => $this->footer,
            'footer_height' => $this->footerHeight,
            'header' => $this->header,
            'header_height' => $this->headerHeight,
            'return_signer_links' => $this->requestSignerLinks,
            'signature_placement' => $this->signaturesOnAllPages ? 1 : 2,
            'signature_type' => $this->certify ? 4 : 1,
            'signers_in_order' => $this->signInOrder
        ];

        // Signers
        $order = 0;
        foreach ($this->signers as $signer) {
            $request['signers'][] = (object)[
                'firstname' => $signer->firstName,
                'lastname' => $signer->lastName,
                'email' => $signer->email,
                'order' => $order++,
                'behalfof' => $signer->behalfOf,
                'message' => $signer->message,
                'extramessage' => $signer->extraMessage
            ];
        }

        // CC
        if (count($this->cc) > 0) {
            $request['cc_emails'] = implode(',', $this->cc);
        }

        // User
        if (isset($this->userId)) {
            $request['user'] = '/api/v1/user/'.$this->userId.'/';
        }

        // Password
        if (isset($this->password)) {
            $request['pdf_password'] = $this->password;
            $request['pdf_password_type'] = $this->storePassword ? 1 : 2;
        }

        return $request;
    }
}
