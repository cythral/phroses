<?php

namespace Phroses\Testing\Integration;

use \Phroses\Testing\TestCase;
use \Phroses\Theme\Loaders\FolderLoader;
use function \Phroses\{ getIncludeOutput };


class FolderLoaderTest extends TestCase {

    /**
     * @dataProvider existsProvider
     */
    public function testExists(FolderLoader $loader, bool $exists) {
        $this->assertEquals($exists, $loader->exists());
    }

    /**
     * @dataProvider typesProvider
     */
    public function testHasType(FolderLoader $loader, array $types) {
        foreach($types as $type => $exists) {
            $this->assertEquals($exists, $loader->hasType($type));
        }
    }

    /**
     * @dataProvider foldersProvider
     */
    public function testHasFolder(FolderLoader $loader, array $folders) {
        foreach($folders as $folder => $exists) {
            $this->assertEquals($exists, $loader->hasFolder($folder));
        }
    }

    /**
     * @dataProvider assetsProvider
     */
    public function testHasAsset(FolderLoader $loader, array $assets) {
        foreach($assets as $asset => $exists) {
            $this->assertEquals($exists, $loader->hasAsset($asset));
        }
    }

    /**
     * @dataProvider errorsProvider
     */
    public function testHasError(FolderLoader $loader, array $errors) {
        foreach($errors as $error => $exists) {
            $this->assertEquals($exists, $loader->hasError($error));
        }
    }

    /**
     * @dataProvider apiProvider
     */
    public function testHasApi(FolderLoader $loader, bool $hasApi) {
        $this->assertEquals($hasApi, $loader->hasApi());
    }

    /**
     * @dataProvider typesProvider
     */
    public function testGetType(FolderLoader $loader, array $types) {
        foreach($types as $type => $exists) {
            if($exists) $this->assertEquals(file_get_contents("{$loader->getPath()}/{$type}.tpl"), $loader->getType($type));
            else $this->assertEquals(null, $loader->getType($type));
        }
    }

    /**
     * @dataProvider typesProvider
     */
    public function testGetTypes(FolderLoader $loader, array $types) {
        $types = array_keys(array_filter($types, function($value, $key) { return $value; }, ARRAY_FILTER_USE_BOTH));
        $this->assertEquals($types, $loader->getTypes(), "\$canonicalize = true", $delta = 0.0, $maxDepth = 10, $canonicalize = true);
    }

    /**
     * @dataProvider assetsProvider
     */
    public function testGetAsset(FolderLoader $loader, array $assets) {
        foreach($assets as $asset => $exists) {
            if($exists) $this->assertEquals(file_get_contents("{$loader->getPath()}/assets/{$asset}"), $loader->getAsset($asset));
            else $this->assertEquals(null, $loader->getAsset($asset));
        }
    }

    /**
     * @dataProvider assetsProvider
     */
    public function testGetAssets(FolderLoader $loader, array $assets) {
        $assets = array_keys(array_filter($assets, function($value, $key) { return $value; }, ARRAY_FILTER_USE_BOTH));
        $this->assertEquals($assets, $loader->getAssets(), "\$canonicalize = true", $delta = 0.0, $maxDepth = 10, $canonicalize = true);
    }

    /**
     * @dataProvider errorsProvider
     */
    public function testGetError(FolderLoader $loader, array $errors) {
        foreach($errors as $error => $exists) {
            if($exists) $this->assertEquals(getIncludeOutput("{$loader->getPath()}/errors/{$error}.php"), $loader->getError($error));
            else $this->assertEquals(null, $loader->getError($error));
        }
    }

    public function testList() {
        $this->assertTrue(array_search("bloom", FolderLoader::list()) !== false);
        $this->assertTrue(array_search("bloom2", FolderLoader::list()) !== false);
    }

    public function existsProvider() {
        return [ 
            [ new FolderLoader("bloom"), true ],
            [ new FolderLoader("bloom2"), true ],
            [ new FolderLoader("helloworld"), false ],
            [ new FolderLoader("foobar"), false ]
        ];
    }

    public function typesProvider() {
        return [
            [ new FolderLoader("bloom"), [ "page" => true, "custom" => true, "flower" => false ] ],
            [ new FolderLoader("bloom2"), [ "page" => true, "custom" => false, "flower" => false ] ],
            [ new FolderLoader("helloworld"), [ "page" => false, "custom" => false, "flower" => false ] ]
        ];
    }

    public function foldersProvider() {
        return [
            [ new FolderLoader("bloom"), [ "assets" => true, "errors" => true, "flower" => false ] ],
            [ new FolderLoader("bloom2"), [ "assets" => true, "errors" => false, "flower" => false ] ],
            [ new FolderLoader("helloworld"), [ "assets" => false, "errors" => false, "flower" => false ] ]
        ];
    }

    public function assetsProvider() {
        return [
            [ new FolderLoader("bloom"), [ "css/style.css" => true, "js/random.js" => false ] ],
            [ new FolderLoader("bloom2"), [ "css/style.css" => true, "js/random.js" => false ] ],
            [ new FolderLoader("helloworld"), [ "css/style.css" => false, "js/random.js" => false ] ]
        ];
    }

    public function errorsProvider() {
        return [
            [ new FolderLoader("bloom"), [ "404" => true, "500" => false ] ],
            [ new FolderLoader("bloom2"), [ "404" => false, "500" => false ] ],
            [ new FolderLoader("helloworld"), [ "404" => false, "500" => false ] ]
        ];
    }

    public function apiProvider() {
        return [
            [ new FolderLoader("bloom"), true ],
            [ new FolderLoader("bloom2"), false ],
            [ new FolderLoader("helloworld"), false ]
        ];
    }
}