<?php

namespace Phroses;


class Email {    
    private $to;
    private $from;
    private $subject;
    private $message;
    public $use_html = true;
    private $headers;

    public function __construct(array $options) {
        $this->options($options);
    }

    private function parseHeaders() {
        // HTML Emails
        if($this->use_html) {
            $this->addHeader("MIME-Version: 1.0");
            $this->addHeader("Content-type: text/html; charset=iso-8859-1");
        }

        // From
        if(isset($this->sender)) $this->addHeader("Sender: $this->sender");
        if(isset($this->from)) $this->addHeader("From: $this->from");
        if(isset($this->replyto)) $this->addHeader("Reply-To: $this->replyto");
    }

    public function addHeader($header) {
        $this->headers .= $header."\r\n";
    }

    public function __invoke() {
        $this->parseHeaders();
        mail($this->to, $this->subject, $this->message, $this->headers);
    }

    private function options(array $options) {
        foreach($options as $key => $val) $this->{$key} = $val;
    }
}