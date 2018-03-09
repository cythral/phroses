<?php

namespace Phroses\Testing;

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
        $dataset = str_replace("{password}", inix::get("test-password"), $dataset);
        $this->dataset = json_decode($dataset);
        $this->insertDataset("sites", $this->dataset);
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
}