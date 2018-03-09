<?php

namespace Phroses\Testing\Theme;

use \Phroses\Testing\TestCase;
use \Phroses\Theme\Theme;
use \Phroses\Theme\Loaders\FolderLoader;
use \Phroses\Theme\Loaders\DummyLoader;
use \Phroses\Exceptions\ThemeException;
use \Phroses\Exceptions\ExitException;

use function \Phroses\{ getIncludeOutput, getTagContents, trueArrayKeys, stripPhyrexFields };
use const \Phroses\{ INCLUDES };

/**
 * This test case tests various functions of the theme class, using
 * the bloom theme in particular.
 * 
 * @covers Phroses\Theme\Theme
 */
class ThemeTest extends TestCase {

    /**
     * the list method is supposed to return a list of all themes in the /themes directory
     * 
     * @covers \Phroses\Theme\Theme::list
     */
    public function testList() {
        DummyLoader::$list = [];
        $this->assertTrue(array_search("bloom", Theme::list()) !== false);
        $this->assertTrue(array_search("bloom2", Theme::list()) !== false);
    }

    /**
     * Tests the creation of a theme that doesn't exist
     */
    public function testInvalidTheme() {
        $this->expectException(ThemeException::class);
        new Theme("foobar"); // foobar theme doesn't exist
    }

    /**
     * Makes sure that the loader is set correctly.  (FolderLoader by default, DummyLoader if manually set)
     * 
     * @covers Phroses\Theme\Theme::getLoader
     * @dataProvider loaderProvider
     */
    public function testLoader(Theme $theme, string $loader) {
        $this->assertInstanceOf($loader, $theme->getLoader());
    }

    /**
     * Takes an array of types (typename => exists) and tests if they exist or not
     * 
     * @covers Phroses\Theme\Theme::hasType
     * @dataProvider typesProvider
     */
    public function testHasType(Theme $theme, array $types) {
        foreach($types as $type => $exists) {
            $this->assertEquals($exists, $theme->hasType($type));
        }
    }

    /**
     * Tests Theme->getTypes()
     * 
     * @covers \Phroses\Theme\Theme::getTypes
     * @dataProvider typesProvider
     */
    public function testGetTypes(Theme $theme, array $types) {
        $this->assertArrayEquals(trueArrayKeys($types), $theme->getTypes());
    }


    /**
     * @covers Phroses\Theme\Theme::setType
     * @dataProvider typesProvider
     */
    public function testSetType(Theme $theme, array $types) {
        foreach($types as $type => $exists) {
            if($exists) {
                $this->assertEquals($type, $theme->setType($type, true));

                // make sure the template was reloaded
                if($type != "redirect") {
                    $this->assertEquals(file_get_contents("{$theme->getPath()}/{$type}.tpl"), $theme->getTpl()); 
                }

            } else {
                $thrown = false;

                try { $theme->setType($type); }
                catch(ThemeException $e) { $thrown = true; }

                $this->assertTrue($thrown);
            }
        }
    }

    /**
     * Takes an array of errors (errorname => exists) and tests whether they exist or not
     * 
     * @covers \Phroses\Theme\Theme::hasError
     * @dataProvider errorsProvider
     */
    public function testHasError(Theme $theme, array $errors) {
        foreach($errors as $error => $exists) {
            $this->assertEquals((bool) $exists, $theme->hasError($error));
        }
    }

    /**
     * @covers \Phroses\Theme\Theme::hasApi
     * @dataProvider apiProvider
     */
    public function testHasApi(Theme $theme, bool $hasApi) {
        $this->assertEquals($hasApi, $theme->hasApi());
    }

    /**
     * @covers \Phroses\Theme\Theme::runApi
     * @dataProvider apiProvider
     */
    public function testRunApi(Theme $theme, bool $hasApi, $output) {
        ob_start();
        try {
            $theme->runApi();
        } catch(ExitException $e) {} // do nothing if trying to exit
            
        $this->assertEquals($output, trim(ob_get_clean()));
    }

    /**
     * Tests the bloom theme to make sure it reads its 404 error correctly.
     * 
     * @covers \Phroses\Theme\Theme::readError
     */
    public function testBloomReadError404() {
        ob_start();
        $theme = new Theme("bloom");
        $theme->readError("404");
        $this->assertEquals(getIncludeOutput(INCLUDES["THEMES"]."/bloom/errors/404.php"), getTagContents(trim(ob_get_clean()), "main"));
    }

    /**
     * Tests the bloom theme to make sure it reads invalid themes correctly. (should output nothing)
     * 
     * @covers \Phroses\Theme\Theme::readError
     */
    public function testBloomReadError503() {
        ob_start();
        $theme = new Theme("bloom");
        $theme->readError("503");
        $this->assertEquals("", trim(ob_get_clean()));
    }

    /**
     * @covers Phroses\Theme\Theme::getContentFields
     * @dataProvider defaultContentFieldsProvider
     */
    public function testGetContentFields(Theme $theme, array $contentFields) {
        $this->assertArrayEquals($contentFields, $theme->getContentFields());
    }

    /**
     * @covers Phroses\Theme\Theme::getEditorFields
     * @dataProvider editorFieldsProvider
     */
    public function testGetEditorFields(Theme $theme, array $content, string $output) {
        $this->assertEquals($output, $theme->getEditorFields(null, $content));
    }

    /**
     * @covers Phroses\Theme\Theme::hasAsset
     * @dataProvider assetsProvider
     */
    public function testHasAsset(Theme $theme, array $assets) {
        foreach($assets as $asset => $exists) {
            $this->assertEquals($exists, $theme->hasAsset($asset));
        }
    }

    public function loaderProvider() {
        return [
            [ new Theme("bloom"), Theme::LOADERS["FOLDER"] ],
            [ new Theme("foobar", "page", null, THEME::LOADERS["DUMMY"]), THEME::LOADERS["DUMMY"] ]
        ];
    }

    public function typesProvider() {
        return [
            [ new Theme("bloom"), [ "page" => true, "custom" => true, "redirect" => true, "faux" => false ] ],
            [ new Theme("bloom2"), [ "page" => true, "custom" => false, "redirect" => true, "faux" => false ] ]
        ]; 
    }

    public function assetsProvider() {
        return [
            [ new Theme("bloom"), [ "css/style.css" => true, "js/main.js" => false ] ],
            [ new Theme("bloom2"), [ "css/style.css" => true, "js/main.js" => false ] ]
        ];
    }

    public function apiProvider() {
        return [
            [ new Theme("bloom"), true, "hi" ],
            [ new Theme("bloom2"), false, "" ]
        ];
    }

    public function errorsProvider() {
        return [
            [ new Theme("bloom"), [ "404" => true, "503" => false ] ],
            [ new Theme("bloom2"), [ "404" => false, "503" => false ] ]
        ];  
    }

    public function defaultContentFieldsProvider() {
        return [
            [ new Theme("bloom"), [ "main" => "editor" ] ],
            [ new Theme("bloom", "custom"), [ "main" => "editor", "author" => "text" ] ]
        ];
    }

    public function editorFieldsProvider() {
        return [
            [ new Theme("bloom"), [], '<div class="form_field content editor" id="page-main" data-id="main"></div>' ],
            [ new Theme("bloom"), [ "main" => "test" ], '<div class="form_field content editor" id="page-main" data-id="main">test</div>' ]
        ];
    }
}