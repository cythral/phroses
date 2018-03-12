<?php

namespace Phroses;

use \Phroses\Exceptions\UploadException;

class Upload {
    /** @var string the site that hosts the upload */
    private $site;

    /** @var string the name of the upload */
    private $_name;

    /** @var string root directory for uploads */
    const ROOT = INCLUDES["UPLOADS"];

    // use dynamic properties
    use \Phroses\Traits\Properties;

    /**
     * Constructs a new Upload object
     * 
     * @param Site $site the site where the upload resides
     * @param string $name the name of the upload
     * @return void
     */
    public function __construct(Site $site, string $name) {
        $this->site = $site;
        $this->_name = $name;
    }

    /**
     * Checks to see if the upload exists in the filesystem
     * 
     * @return bool true if the upload exists and false if not
     */
    public function exists(): bool {
        return file_exists($this->path);
    }

    /**
     * Displays the upload
     * 
     * @return void
     */
    public function display(): void {
        readfileCached($this->path);
    }

    /**
     * Renames the upload
     * 
     * @param string $name what to rename the upload to
     * @return bool true on success and false on failure
     */
    public function rename(string $name): bool {
        if((new Upload($this->site, $name))->exists()) {
            throw new UploadException("resource_exists");
        }
        
        if(@rename($this->path, dirname($this->path)."/{$name}")) {
            $this->_name = $name;
            return true;
        }

        return false;
    }

    /**
     * Deletes the upload
     * 
     * @return bool true on success and false on failure
     */
    public function delete(): bool {
        if(!$this->exists()) {
            throw new UploadException("resource_missing");
        }

        return unlink($this->path);
    }
    
    /**
     * Getter for $this->path
     * 
     * @return string the full pathname of the upload
     */
    protected function getPath(): string {
        return self::ROOT."/{$this->site->url}/{$this->name}";
    }

    /**
     * Setter for the name property, same as doing Upload::rename
     * 
     * @param string $value the name to set the upload to
     * @return void
     */
    protected function setName(string $value): void {
        $this->rename($value);
    }

    /**
     * Getter for the name property
     * 
     * @return string the name of the upload
     */
    protected function getName(): string {
        return $this->_name;
    }

    /**
     * Creates a new upload
     * 
     * @param Site $site the site to create an upload for
     * @param string $name the name of the upload to create
     * @param array $from the file array (taken from $_FILES)
     * @param bool $useRename if true, this will use rename instead of move_uploaded_file (used primarily in testing)
     * @return Upload the created upload
     */
    static public function create(Site $site, string $name, array $from, bool $useRename = false): Upload {
        if(!file_exists(self::ROOT) && !@mkdir(self::ROOT)) {
            throw new UploadException("topupldir_notfound");
        }

        if(!file_exists(self::ROOT."/".$site->url) && !@mkdir(self::ROOT."/".$site->url)) {
            throw new UploadException("siteupldir_notfound");
        }

        if((new Upload($site, $name))->exists()) {
            throw new UploadException("resource_exists");
        }

        if(in_array($from["error"], [ UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ])) {
            throw new UploadException("large_file");
        }

        if(($useRename && !@rename($from["tmp_name"], self::ROOT."/{$site->url}/{$name}")) || (!$useRename && !move_uploaded_file($from["tmp_name"], self::ROOT."/{$site->url}/{$name}"))) {
            throw new UploadException("failed_upl");
        } 

        return new self($site, $name);
    }

    /**
     * Returns a list of uploads in a site
     * 
     * @param Site $site the site to list uploads for
     * @return array an array of upload objects
     */
    static public function list(Site $site): array {
        return array_map(function($value) use ($site) { return new Upload($site, basename($value)); }, glob(self::ROOT."/{$site->url}/*"));
    }
}