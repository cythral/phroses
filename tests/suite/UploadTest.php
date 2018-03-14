<?php

namespace Phroses\Testing;

use \Phroses\Site;
use \Phroses\Upload;
use \Phroses\Exceptions\UploadException;

use const Phroses\{ ROOT };

class UploadTest extends TestCase {

    /**
     * setUp fixture, creates upload directories and files for testing
     */
    public function setUp() {
        shell_exec("rm -rf ".ROOT."/uploads");
        clearstatcache();

        mkdir(ROOT."/uploads");
        mkdir(ROOT."/uploads/localhost");
        touch(ROOT."/uploads/localhost/test.png");
        touch(ROOT."/uploads/localhost/logo.png");
        touch(ROOT."/temp");

        $this->site = new Site([
            "id" => null,
            "url" => "localhost",
            "theme" => "bloom",
            "name" => null,
            "adminUsername" => null,
            "adminPassword" => null,
            "adminURI" => "/admin",
            "maintenance" => false
        ]);
    }

    /**
     * tearDown fixture, remove all upload directories and files used in testing
     */
    public function tearDown() {
        shell_exec("rm -rf ".ROOT."/uploads");
        clearstatcache();
        if(file_exists(ROOT."/temp")) unlink(ROOT."/temp");
        unset($this->site);
    }

    /**
     * Exists should return true when an upload exists
     * 
     * @covers \Phroses\Upload::exists
     */
    public function testExistsTrue() {
        $upload = new Upload($this->site, "test.png");
        $this->assertTrue($upload->exists());
    }

    /**
     * Exists should return false when an upload doesn't exist
     * 
     * @covers \Phroses\Upload::exists
     */
    public function testExistsFalse() {
        $upload = new Upload($this->site, "test.jpg");
        $this->assertFalse($upload->exists());
    }

    /**
     * Rename should return true when renaming to a file that doesn't exist
     * 
     * @covers \Phroses\Upload::rename
     */
    public function testRenameSuccess() {
        $upload = new Upload($this->site, "test.png");
        $this->assertTrue($upload->rename("test2.png"));
    }

    /**
     * Rename should throw an exception when trying to rename to a file that
     * already exists.
     * 
     * @covers \Phroses\Upload::rename
     */
    public function testRenameException() {
        $this->expectException(UploadException::class);
        $upload = new Upload($this->site, "test.png");
        $upload->rename("logo.png");
    }

    /**
     * On uploads that exist, delete should return true and no exception should
     * be thrown
     * 
     * @covers \Phroses\Upload::delete
     */
    public function testDeleteSuccess() {
        $upload = new Upload($this->site, "test.png");
        $this->assertTrue($upload->delete());
    }

    /**
     * On uploads that don't exist, delete should throw an UploadException
     * 
     * @covers \Phroses\Upload::delete
     */
    public function testDeleteException() {
        $this->expectException(UploadException::class);
        $upload = new Upload($this->site, "test2.png");
        $upload->delete();
    }

    /**
     * Create should return an upload object on success and throw no Exception
     * 
     * @covers \Phroses\Upload::create
     */
    public function testCreate() {
        $from = [
            "tmp_name" => ROOT."/temp",
            "error" => 0
        ];

        $this->assertInstanceOf(Upload::class, Upload::create($this->site, "new.png", $from, true));
    }

    /**
     * List should return an array of uploads
     * 
     * @covers \Phroses\Upload::list
     */
    public function testList() {
        $this->assertArrayType(Upload::list($this->site), Upload::class);
    }
}