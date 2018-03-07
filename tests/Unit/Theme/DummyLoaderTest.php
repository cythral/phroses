<?php

use \PHPUnit\Framework\TestCase;
use \Phroses\Theme\Loaders\DummyLoader;

class DummyLoaderTest extends TestCase {
    
    /**
     * @dataProvider providerExists
     */
    public function testExists(DummyLoader $loader, bool $exists) {
       $this->assertEquals($exists, $loader->exists());
    }

    /**
     * @dataProvider providerTypes
     */
    public function testHasType(DummyLoader $loader, array $types) {
        foreach($types as $type => $exists) {
            $this->assertEquals($exists, $loader->hasType($type));
        }
    }

     /**
     * @dataProvider providerFolders
     */
    public function testHasFolder(DummyLoader $loader, array $folders) {
        foreach($folders as $folder => $exists) {
            $this->assertEquals($exists, $loader->hasFolder($folder));
        }
    }

    /**
     * @dataProvider providerAssets
     */
    public function testHasAsset(DummyLoader $loader, array $assets) {
        foreach($assets as $asset => $exists) {
            $this->assertEquals($exists, $loader->hasAsset($asset));
        }
    }

    /**
     * @dataProvider providerErrors
     */
    public function testHasError(DummyLoader $loader, array $errors) {
        foreach($errors as $error => $exists) {
            $this->assertEquals($exists, $loader->hasError($error));
        }
    }

    /**
     * @dataProvider providerHasApi
     */
    public function testHasApi(DummyLoader $loader, bool $hasApi) {
        $this->assertEquals($hasApi, $loader->hasApi());
    }

    /**
     * @dataProvider providerTypes
     */
    public function testGetType(DummyLoader $loader, array $types) {
        foreach($types as $type => $value) {
            $this->assertEquals($value || null, $loader->getType($type));
        }
    }

    /**
     * @dataProvider providerAssets
     */
    public function testGetAsset(DummyLoader $loader, array $assets) {
        foreach($assets as $asset => $value) {
            $this->assertEquals($value || null, $loader->getAsset($asset));
        }
    }

    /**
     * @dataProvider providerErrors
     */
    public function testGetError(DummyLoader $loader, array $errors) {
        foreach($errors as $error => $value) {
            $this->assertEquals($value || null, $loader->getError($error));
        }
    }

    /**
     * @dataProvider providerAssets
     */
    public function testGetAssets(DummyLoader $loader, array $assets) {
        $assets = array_keys(array_filter($assets, function($value) { return $value; }));
        $this->assertEquals($assets, $loader->getAssets());
    }

    public function testList() {
        $this->assertEquals([], DummyLoader::list());
        DummyLoader::$list[] = "bloom";
        $this->assertEquals([ "bloom" ], DummyLoader::list());
    }


    public function providerExists() {
        return [
            [ new DummyLoader([ "exists" => true ]), true ],
            [ new DummyLoader([ "exists" => false ]), false ]
        ];
    }

    

    public function providerTypes() {
        return [
            [ new DummyLoader([ "types" => [ "page" => true, "custom" => true ] ]), [ "page" => true, "custom" => true, "foobar" => false ] ],
            [ new DummyLoader([ "types" => [ "page" => true ] ]),                   [ "page" => true, "custom" => false, "foobar" => false ] ],
            [ new DummyLoader([ "types" => [ ] ]),                                  [ "page" => false, "custom" => false, "foobar" => false ] ],
        ];
    }

   

    public function providerFolders() {
        return [
            [ new DummyLoader([ "folders" => [ "assets", "errors" ] ]), [ "assets" => true, "errors" => true, "foobar" => false ] ],
            [ new DummyLoader([ "folders" => [ "assets" ] ]),           [ "assets" => true, "errors" => false, "foobar" => false ] ],
            [ new DummyLoader([ "folders" => [ ] ]),                    [ "assets" => false, "errors" => false, "foobar" => false ] ]
        ];
    }

    

    public function providerAssets() {
        return [
            [ new DummyLoader([ "assets" => [ "style.css" => true, "main.js" => true ] ]), [ "style.css" => true, "main.js" => true, "logo.png" => false ] ],
            [ new DummyLoader([ "assets" => [ "style.css" => true ] ]),                    [ "style.css" => true, "main.js" => false, "logo.png" => false ] ],
            [ new DummyLoader([ "assets" => [ ] ]),                                        [ "style.css" => false, "main.js" => false, "logo.png" => false ] ],
        ];
    }

    

    public function providerErrors() {
        return [
            [ new DummyLoader([ "errors" => [ "404" => true, "500" => true ] ]), [ "404" => true, "500" => true, "503" => false ] ],
            [ new DummyLoader([ "errors" => [ "404" => true ] ]),                [ "404" => true, "500" => false, "503" => false ] ],
            [ new DummyLoader([ "errors" => [ ] ]),                              [ "404" => false, "500" => false, "503" => false ] ],
        ];
    }

    public function providerHasApi() {
        return [
            [ new DummyLoader([ "api" => true ]), true ],
            [ new DummyLoader([ "api" => false ]), false ]
        ];
    }
}