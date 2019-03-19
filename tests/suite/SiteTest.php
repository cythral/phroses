<?php

namespace Phroses\Testing;

use \Phroses\Page;
use \Phroses\Site;
use \inix\Config as inix;
use \Phroses\Exceptions\ReadOnlyException;

/**
 * @covers \Phroses\Site
 */
class SiteTest extends TestCase {

    /**
     * Setup fixture, inserts dataset into the database
     */
    public function setUp(): void {
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
    public function tearDown(): void {
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
        $this->expectException(ReadOnlyException::class);
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

    /**
     * The uploads property should be the equivalent of calling
     * Upload::list, which returns an array of upload objects
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::getUploads
     */
    public function testGetUploads() {
        $this->assertArrayType(Site::generate(1)->uploads, Upload::class);
    }

    /**
     * Setting the uploads property should throw an exception
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::setUploads
     */
    public function testSetUploadsException() {
        $this->expectException(ReadOnlyException::class);
        Site::generate(1)->uploads = [];
    }

    /**
     * Getting the views property should return the total amount of views
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::getViews
     */
    public function testGetViews() {
        $this->assertEquals(2, Site::generate(1)->views);
    }


    /**
     * Setting the views property should throw a readonlyexception
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::setViews
     */
    public function testSetViews() {
        $this->expectException(ReadOnlyException::class);
        Site::generate(1)->views = 5;
    }

    /**
     * Getting the pageCount property should return the total amount of pages
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::getPageCount
     */
    public function testGetPageCount() {
        $this->assertEquals(1, Site::generate(1)->pageCount);
    }

    /**
     * Setting the pageCount property should throw an exception
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::setPageCount
     */
    public function testSetPageCount() {
        $this->expectException(ReadOnlyException::class);
        Site::generate(1)->pageCount = 5;
    }

    /**
     * The getter for adminIP should return an array of ip addresses
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::getAdminIp
     */
    public function testGetAdminIp() {
        $this->assertArrayEquals(["192.168.0.1", "10.8.0.1", "10.9.0.0/27"], (array)Site::generate(1)->adminIP);
    }

    /**
     * Makes sure the setter for adminIP works as expected
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::setAdminIp
     */
    public function testSetAdminIp() {
        $site = Site::generate(1);
        $site->adminIP = ["192.168.0.1"];
        $this->assertArrayEquals(["192.168.0.1"], (array)$site->adminIP);
    }

    /**
     * When appending to the adminIP arrayobject, it should call the setter so changes persist
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::getAdminIp
     */
    public function testAddAdminIp() {
        $site = Site::generate(1);
        $site->adminIP->append("192.168.0.2");
        $this->assertArrayEquals(["192.168.0.1", "10.8.0.1", "10.9.0.0/27", "192.168.0.2"], (array)$site->adminIP);
    }

    /**
     * ipHasAccess should return true when the specified ip address is in the adminIP arrayobject
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::ipHasAccess
     */
    public function testIpHasAccessTrue() {
        $this->assertTrue(Site::generate(1)->ipHasAccess("192.168.0.1"));
    }

    /**
     * ipHasAccess should return false when the specified ip address is not in the arrayobject and it is not empty
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::ipHasAccess
     */
    public function testIpHasAccessFalse() {
        $this->assertFalse(Site::generate(1)->ipHasAccess("192.168.0.2"));
    }

    /**
     * If the adminIP arrayobject is empty, all ips should have access
     * 
     * @depends testGenerateValidId
     * @depends testSetAdminIp
     * @covers \Phroses\Site::ipHasAccess
     */
    public function testIpHasAccessEmpty() {
        $site = Site::generate(1);
        $site->adminIP = [];
        $this->assertTrue($site->ipHasAccess("8.8.8.8"));
    }

    /**
     * ipHasAccess should return true when testing with an ips in a range specified within the adminIP array
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::ipHasAccess
     */
    public function testIpHasAccessRange() {
        $site = Site::generate(1);
        $this->assertTrue($site->ipHasAccess("10.9.0.1"));
    }

    /**
     * ipHasAccess should return false when testing with an ip that is not in a range specified in the adminIP array
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::ipHasAccess
     */
    public function testIpHasAccessOutsideRange() {
        $site = Site::generate(1);
        $this->assertFalse($site->ipHasAccess("10.9.0.32"));
    }

    /**
     * ipHasAccess should work with ipv6 ranges
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::ipHasAccess
     */
    public function testIpHasAccessRange6() {
        $site = Site::generate(1);
        $site->adminIP = ["fdc9:f765:1356:acd7::/64"];
        $this->assertTrue($site->ipHasAccess("fdc9:f765:1356:acd7::1"));
    }

    /**
     * ipHasAccess should work with ipv6 ranges
     * 
     * @depends testGenerateValidId
     * @covers \Phroses\Site::ipHasAccess
     */
    public function testIpHasAccessOutsideRange6() {
        $site = Site::generate(1);
        $site->adminIP = ["fdc9:f765:1356:acd7::/64"];
        $this->assertFalse($site->ipHasAccess("fdc9:f765:1356:ffff::1/64"));
    }
}