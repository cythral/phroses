<?php

namespace Phroses\Testing\Integration;

use \Phroses\Testing\TestCase;
use \Phroses\Theme\Theme;
use \Phroses\Theme\Loaders\FolderLoader;
use \Phroses\Theme\Loaders\DummyLoader;
use \Phroses\Exceptions\ThemeException;

class ThemeTest extends TestCase {

    public function testList() {
        DummyLoader::$list = [];
        $this->assertArrayEquals(["bloom", "bloom2"], Theme::list());
    }

    
    public function testInvalidTheme() {
        $this->expectException(ThemeException::class);
        new Theme("foobar"); // foobar theme doesn't exist
    }

    /**
     * @dataProvider loaderProvider
     */
    public function testLoader(Theme $theme, string $loader) {
        $this->assertInstanceOf($loader, $theme->getLoader());
    }

    /**
     * @dataProvider typesProvider
     */
    public function testHasType(Theme $theme, array $types) {
        foreach($types as $type) {
            $this->assertTrue($theme->hasType($type));
        }
    }

    /**
     * @dataProvider typesProvider
     */
    public function testGetTypes(Theme $theme, array $types) {
        $this->assertArrayEquals($types, $theme->getTypes());
    }

    /**
     * @dataProvider apiProvider
     */
    public function testHasApi(Theme $theme, bool $hasApi) {
        $this->assertEquals($hasApi, $theme->hasApi());
    }

    public function loaderProvider() {
        return [
            [ new Theme("bloom"), Theme::LOADERS["FOLDER"] ],
            [ new Theme("foobar", "page", null, THEME::LOADERS["DUMMY"]), THEME::LOADERS["DUMMY"] ]
        ];
    }

    public function typesProvider() {
        return [
            [ new Theme("bloom"), [ "page", "custom", "redirect" ] ],
            [ new Theme("bloom2"), [ "page", "redirect" ] ]
        ]; 
    }

    public function apiProvider() {
        return [
            [ new Theme("bloom"), true ],
            [ new Theme("bloom2"), false ]
        ];
    }
}