<?php
/**
 * This is a simple OO wrapper for php's mail function
 */

namespace Phroses;


class Email {    
    private $to;
    private $from;
    private $subject;
    private $message;
    public $useHtml = true;
    private $headers;

    /**
     * Constructor, takes an array of email options and sets them
     * to their respective properties
     * 
     * @param array $options an array of options to use for the email
     */
    public function __construct(array $options) {
        foreach($options as $key => $val) $this->{$key} = $val;
    }

    /**
     * Parses headers and sends the email
     * 
     * @return bool true on success and false on failure
     */
    public function __invoke(): bool {
        $this->parseHeaders();
        return mail($this->to, $this->subject, $this->message, $this->headers);
    }

    /**
     * Wrapper for __invoke(); sends the email
     * 
     * @return bool true on success and false on failure
     */
    public function send(): bool {
        return $this->__invoke();
    }

    /**
     * Adds a header to the email
     */
    private function addHeader($header) {
        $this->headers .= $header."\r\n";
    }

    /**
     * Turns various properties into headers
     */
    private function parseHeaders() {
        // HTML Emails
        if($this->useHtml) {
            $this->addHeader("MIME-Version: 1.0");
            $this->addHeader("Content-type: text/html; charset=iso-8859-1");
        }

        // From
        if(isset($this->sender)) $this->addHeader("Sender: $this->sender");
        if(isset($this->from)) $this->addHeader("From: $this->from");
        if(isset($this->replyto)) $this->addHeader("Reply-To: $this->replyto");
    }
}