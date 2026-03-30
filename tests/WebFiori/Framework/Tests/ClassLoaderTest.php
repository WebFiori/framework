<?php
namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Autoload\ClassLoader;
use WebFiori\Framework\Autoload\ClassLoaderException;

class ClassLoaderTest extends TestCase {

    // ── isValidNamespace ────────────────────────────────────────────────────

    /** @test */
    public function testIsValidNamespace_root() {
        $this->assertTrue(ClassLoader::isValidNamespace('\\'));
    }

    /** @test */
    public function testIsValidNamespace_simple() {
        $this->assertTrue(ClassLoader::isValidNamespace('App'));
    }

    /** @test */
    public function testIsValidNamespace_nested() {
        $this->assertTrue(ClassLoader::isValidNamespace('WebFiori\\Framework\\Autoload'));
    }

    /** @test */
    public function testIsValidNamespace_startsWithDigit() {
        $this->assertFalse(ClassLoader::isValidNamespace('1Bad'));
    }

    /** @test */
    public function testIsValidNamespace_invalidChar() {
        $this->assertFalse(ClassLoader::isValidNamespace('Bad-NS'));
    }

    // ── live-instance basics ────────────────────────────────────────────────

    /** @test */
    public function testRoot() {
        $this->assertEquals(ROOT_PATH, ClassLoader::root());
    }

    /** @test */
    public function testGetCachePath() {
        $path = ClassLoader::getCachePath();
        $this->assertStringEndsWith(ClassLoader::CACHE_NAME, $path);
    }

    /** @test */
    public function testGetFolders_nonEmpty() {
        $this->assertNotEmpty(ClassLoader::getFolders());
    }

    /** @test */
    public function testGetLoadedClasses_nonEmpty() {
        $this->assertNotEmpty(ClassLoader::getLoadedClasses());
    }

    /** @test */
    public function testGetCacheArray_isArray() {
        $this->assertIsArray(ClassLoader::getCacheArray());
    }

    // ── isLoaded ────────────────────────────────────────────────────────────

    /** @test */
    public function testIsLoaded_withoutNs() {
        $this->assertTrue(ClassLoader::isLoaded('ClassLoader'));
    }

    /** @test */
    public function testIsLoaded_withNs() {
        $this->assertTrue(ClassLoader::isLoaded('ClassLoader', 'WebFiori\\Framework\\Autoload'));
    }

    /** @test */
    public function testIsLoaded_wrongNs() {
        $this->assertFalse(ClassLoader::isLoaded('ClassLoader', 'Wrong\\NS'));
    }

    /** @test */
    public function testIsLoaded_unknownClass() {
        $this->assertFalse(ClassLoader::isLoaded('TotallyNonExistentClass99'));
    }

    // ── getClassPath ────────────────────────────────────────────────────────

    /** @test */
    public function testGetClassPath_noNs() {
        $paths = ClassLoader::getClassPath('ClassLoader');
        $this->assertNotEmpty($paths);
    }

    /** @test */
    public function testGetClassPath_withNs() {
        $paths = ClassLoader::getClassPath('ClassLoader', 'WebFiori\\Framework\\Autoload');
        $this->assertCount(1, $paths);
    }

    /** @test */
    public function testGetClassPath_wrongNs_returnsEmpty() {
        $paths = ClassLoader::getClassPath('ClassLoader', 'Wrong\\NS');
        $this->assertEmpty($paths);
    }

    // ── newSearchFolder ─────────────────────────────────────────────────────

    /** @test */
    public function testNewSearchFolder_addsFolder() {
        $before = count(ClassLoader::getFolders());
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cl_test_' . time();
        mkdir($tmpDir);

        ClassLoader::newSearchFolder($tmpDir, false);

        $this->assertGreaterThan($before, count(ClassLoader::getFolders()));
        rmdir($tmpDir);
    }

    // ── map / addClassMap ───────────────────────────────────────────────────

    /** @test */
    public function testMap_loadsClassFromFile() {
        $className = 'TempMappedClass' . time();
        $tmpFile   = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $className . '.php';

        file_put_contents($tmpFile, "<?php\nclass $className {}");

        ClassLoader::map($className, $className, $tmpFile);

        $this->assertTrue(class_exists($className, false));
        unlink($tmpFile);
    }

    /** @test */
    public function testAddClassMap_returnsFalse_whenFileNotExist() {
        $result = ClassLoader::get()->addClassMap('Ghost', 'Ghost', '/nonexistent/Ghost.php');
        $this->assertFalse($result);
    }

    /** @test */
    public function testAddClassMap_returnsTrue_whenFileExists() {
        $className = 'TempMappedClass2' . time();
        $tmpFile   = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $className . '.php';
        file_put_contents($tmpFile, "<?php\nclass $className {}");

        $result = ClassLoader::get()->addClassMap($className, $className, $tmpFile);

        $this->assertTrue($result);
        unlink($tmpFile);
    }

    // ── mapAll ──────────────────────────────────────────────────────────────

    /** @test */
    public function testMapAll_loadsMultipleClasses() {
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mapall_' . time();
        mkdir($tmpDir);

        $names = ['MapAllA' . time(), 'MapAllB' . time()];
        foreach ($names as $name) {
            file_put_contents($tmpDir . DIRECTORY_SEPARATOR . $name . '.php', "<?php\nclass $name {}");
        }

        ClassLoader::mapAll('', $tmpDir, $names);

        foreach ($names as $name) {
            $this->assertTrue(class_exists($name, false));
            unlink($tmpDir . DIRECTORY_SEPARATOR . $name . '.php');
        }
        rmdir($tmpDir);
    }

    // ── setOnFail ───────────────────────────────────────────────────────────

    /** @test */
    public function testSetOnFail_doNothing_noException() {
        ClassLoader::setOnFail('do-nothing');
        // Trigger autoload of a non-existent class — should not throw
        class_exists('\\AbsolutelyNonExistent' . time());
        $this->assertTrue(true); // reached here = pass
        ClassLoader::setOnFail('throw-exception'); // restore
    }

    /** @test */
    public function testSetOnFail_throwException() {
        ClassLoader::setOnFail('throw-exception');
        $this->expectException(ClassLoaderException::class);
        class_exists('\\AbsolutelyNonExistent' . time()); // triggers spl_autoload
    }

    /** @test */
    public function testSetOnFail_callable_isCalled() {
        $called = false;
        ClassLoader::setOnFail(function() use (&$called) {
            $called = true;
        });

        class_exists('\\AbsolutelyNonExistent' . time());

        $this->assertTrue($called);
        ClassLoader::setOnFail('throw-exception'); // restore
    }
}
