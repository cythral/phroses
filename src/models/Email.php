<?php
/**
 * This is a simple OO wrapper for php's mail function
 * @todo move this to cythral/utils
 */

namespace Phroses;


class Email { 
    public $to;
    public $from;
    public $subject;
    public $message;
    public $replyTo;
    public $headers = [];

    public $useHtml = true;

    /**
     * Constructor, takes an array of email options and sets them
     * to their respective properties
     * 
     * @param array $options an array of options to use for the email
     */
    public function __construct(array $options) {
        [ 
            "to" => $this->to, 
            "from" => $this->from, 
            "subject" => $this->subject, 
            "message" => $this->message

        ] = $options;

        foreach([ "headers", "replyTo", "useHtml" ] as $optional) {
            if(isset($options[$optional])) $this->{$optional} = $options[$optional];
        }
    }

    /**
     * Parses headers and sends the email
     * 
     * @return bool true on success and false on failure
     */
    public function __invoke(): bool {
        return mail($this->to, $this->subject, $this->message, $this->getParsedHeaders());
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
     * Turns various properties into headers
     */
    private function setupHeaders(): void {
        // HTML Emails
        if($this->useHtml) {
            $this->headers["mime-version"] =  "1.0";
            $this->headers["content-type"] = "text/html; charset=iso-8859-1";

        } else unset($this->headers["mime-version"], $this->headers["content-type"]);
        

        if(isset($this->sender)) $this->headers["sender"] = $this->sender;
        if(isset($this->from)) $this->headers["from"] = $this->from;
        if(isset($this->replyTo)) $this->headers["reply-to"] = $this->replyTo;
    }

    /**
     * Iterates through all headers and parses them into a string
     * for the headers parameter in php's mail()
     * 
     * @return string the parsed headers
     */
    public function getParsedHeaders(): string {
        $this->setupHeaders();
        $parsed = "";

        foreach($this->headers as $key => $val) {
            $parsed .= "{$key}: {$val}\r\n";
        }

        return trim($parsed);
    }
}