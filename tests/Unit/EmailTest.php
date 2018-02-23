<?php
/**
 * @covers \Phroses\Email
 */


use \Phroses\Testing\TestCase;
use \Phroses\Email;

class EmailTest extends TestCase {

    /**
     * @covers \Phroses\Email::getParsedHeaders
     * @dataProvider getParsedHeadersProvider
     */
    public function testGetParsedHeaders(Email $email, string $parsedHeaders) {
        $this->assertEquals($parsedHeaders, $email->getParsedHeaders());
    }

    /**
     * @covers \Phroses\Email::send
     */
    public function testSend() {
        $email = new Email([
            "to" => "phroses.testing@cythral.com",
            "from" => "testing@phroses.com",
            "subject" => "Email Send Test",
            "message" => "<h1>This is a test email.</h1><p>This email was sent to test Phroses' email functionality</p>"
        ]);

        $this->assertTrue($email->send());
    } 

    /**
     * Provider for testGetParsedHeaders
     */
    public function getParsedHeadersProvider() {
        return [
            [ 
                new Email([ "to" => "", "from" => "john@doe.com", "subject" => "", "message" => "" ]),
                "mime-version: 1.0\r\ncontent-type: text/html; charset=iso-8859-1\r\nfrom: john@doe.com"
            ],
            
            // without html
            [
                new Email([ "to" => "", "from" => "john@doe.com", "subject" => "", "message" => "", "useHtml" => false ]),
                "from: john@doe.com"
            ]
        ];
    }
}