<?php

namespace Phroses\Testing;

use \Phroses\Page;
use \Phroses\Site;
use \inix\Config as inix;


/**
 * @covers \Phroses\Site
 */
class SiteTest extends TestCase {

    /**
     * Setup fixture, inserts dataset into the database
     */
    public function setUp() {
        $this->db = $this->getDatabase();
        $dataset = file_get_contents(\Phroses\INCLUDES["TESTS"]."/datasets/sites.json");
        $dataset = str_replace("{password}", inix::get("test-password.hash"), $dataset);
        $this->dataset = json_decode($dataset);

        $this->insertDataset("sites", $this->dataset);
        $this->insertDataset("pages", json_decode(file_get_contents(\Phroses\INCLUDES["TESTS"]."/datasets/pages.json")));
    }

    /**
     * Teardown fixture, closes database connection
     */
    public function tearDown() {
        unset($this->db);
    }

    /**
     * setAdminPassword is an internal setter used by the dataclass, make sure
     * it is hashing passwords according to plan
     * 
     * @covers \Phroses\Site::setAdminPassword
     */
    public function testSetAdminPassword() {
        $site = new Site((array)$this->dataset[0]);
        $site->adminPassword = "test";
        $this->assertTrue(password_verify(inix::get("pepper")."test", $site->adminPassword));
    }

    /**
     * Generate should return a site when given a valid id
     * 
     * @covers \Phroses\Site::generate
     */
    public function testGenerateValidId() {
        $this->assertInstanceOf(Site::class, Site::generate(1));
    }

    /**
     * Generate should return null when given an invalid id
     * 
     * @covers \Phroses\Site::generate
     */
    public function testGenerateInvalidId() {
        $this->assertNull(Site::generate(0));
    }

    /**
     * Generate should return a site when given a valid url
     * 
     * @covers \Phroses\Site::generate
     */
    public function testGenerateValidUrl() {
        $this->assertInstanceOf(Site::class, Site::generate("phroses.dev"));
    }

    /**
     * Generate should return null when given an invalid url
     * 
     * @covers \Phroses\Site::generate
     */
    public function testGenerateInvalidUrl() {
        $this->assertNull(Site::generate("localhost"));
    }

    /**
     * Create should return a site on success
     * 
     * @covers \Phroses\Site::create
     */
    public function testCreateValid() {
        $site = Site::create("phroses-testing", "localhost", "bloom", "/admin", "root", "password");
        $this->assertInstanceOf(Site::class, $site);
    }

    /**
     * Create should return null if inserting an invalid site. This inserts a duplicate url (was inserted in setUp)
     * 
     * @covers \Phroses\Site::create
     */
    public function testCreateDuplicate() {
        $site = Site::create("phroses-testing", "phroses.dev", "bloom", "/admin", "root", "password");
        $this->assertNull($site);
    }

    /**
     * List should return a list of sites id => url
     * 
     * @covers \Phroses\Site::list
     */
    public function testList() {
        $this->assertEquals([ 1 => "phroses.dev" ], Site::list());
    }

    /**
     * Login should work good with username and password from the dataset
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::login
     */
    public function testLoginSuccess() {
        $site = Site::generate(1);
        $username = $this->dataset[0]->adminUsername;
        $password = inix::get("test-password.text");

        $this->assertTrue($site->login($username, $password));
    }

    /**
     * Login should fail, bad password given
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::login
     */
    public function testLoginFailure() {
        $site = Site::generate(1);
        $username = $this->dataset[0]->adminUsername;
        $password = "thisisabadpassword1234";

        $this->assertFalse($site->login($username, $password));
    }

    /**
     * The getter for the pages property should return an array
     * of only \Phroses\Page objects
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::getPages
     */
    public function testGetPagesReturnValue() {
        $this->assertArrayType(Site::generate(1)->pages, Page::class);
    }

    /**
     * The array returned by the pages property should have keys equal to
     * their respective page's uri
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::getPages
     */
    public function testGetPagesKeys() {
        $pages = Site::generate(1)->pages;

        foreach($pages as $uri => $page) {
            $this->assertEquals($page->uri, $uri);
        }
    }

    /**
     * The setter for the pages property should always throw an exception,
     * pages is readonly
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::setPages
     */
    public function testSetPagesException() {
        $this->expectException(\Exception::class);
        Site::generate(1)->pages = [];
    }

    /**
     * getPage should return a valid page instance if the page exists
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::getPage
     */
    public function testGetPageReturnInstance() {
        $this->assertInstanceOf(Page::class, Site::generate(1)->getPage("/"));
    }

    /**
     * getPage should return null if the selected page does not exist
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::getPage
     */
    public function testGetPageReturnNull() {
        $this->assertNull(Site::generate(1)->getPage("/nonexistent"));
    }

    /**
     * hasPage should return true if the selected page exists
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::hasPage
     */
    public function testHasPageTrue() {
        $this->assertTrue(Site::generate(1)->hasPage("/"));
    }

    /**
     * hasPage should return false if the selected page does not exist
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::hasPage
     */
    public function testHasPageFalse() {
        $this->assertFalse(Site::generate(1)->hasPage("/nonexistent"));
    }
}