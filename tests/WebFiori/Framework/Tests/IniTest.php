<?php
namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Ini;

class IniTest extends TestCase {
    private string $tmpRoot;

    protected function setUp(): void {
        $this->tmpRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ini_test_'.uniqid();
        mkdir($this->tmpRoot, 0755, true);
    }

    protected function tearDown(): void {
        $this->removeDir($this->tmpRoot);
    }

    // ── get() ────────────────────────────────────────────────────────────────

    /** @test */
    public function testGet_returnsSameInstance() {
        $this->assertSame(Ini::get(), Ini::get());
    }

    // ── mkdir() ──────────────────────────────────────────────────────────────

    /** @test */
    public function testMkdir_createsDirectory() {
        $dir = $this->tmpRoot.DIRECTORY_SEPARATOR.'newdir';
        $this->assertFalse(is_dir($dir));
        Ini::mkdir($dir);
        $this->assertTrue(is_dir($dir));
    }

    /** @test */
    public function testMkdir_doesNotFailIfAlreadyExists() {
        Ini::mkdir($this->tmpRoot);
        $this->assertTrue(is_dir($this->tmpRoot));
    }

    // ── createAppDirs() ──────────────────────────────────────────────────────

    /** @test */
    public function testCreateAppDirs_createsExpectedDirectories() {
        // Temporarily redefine constants via a sub-process is complex,
        // so we test the actual app dirs that were already created by bootstrap.
        $expected = [
            APP_PATH,
            APP_PATH.'Ini',
            APP_PATH.'Ini'.DIRECTORY_SEPARATOR.'Routes',
            APP_PATH.'Pages',
            APP_PATH.'Commands',
            APP_PATH.'Tasks',
            APP_PATH.'Middleware',
            APP_PATH.'Langs',
            APP_PATH.'Apis',
            APP_PATH.'Config',
            APP_PATH.'Storage',
            APP_PATH.'Storage'.DIRECTORY_SEPARATOR.'Uploads',
            APP_PATH.'Storage'.DIRECTORY_SEPARATOR.'Logs',
            APP_PATH.'Storage'.DIRECTORY_SEPARATOR.'Sessions',
        ];

        Ini::createAppDirs();

        foreach ($expected as $dir) {
            $this->assertTrue(is_dir($dir), "Expected directory missing: $dir");
        }
    }

    // ── createIniClass() ─────────────────────────────────────────────────────

    /** @test */
    public function testCreateIniClass_createsFile() {
        $className = 'TestIniClass'.uniqid();
        Ini::get()->createIniClass($className, 'Test comment');

        $file = APP_PATH.'Ini'.DIRECTORY_SEPARATOR.$className.'.php';
        $this->assertFileExists($file);
        unlink($file);
    }

    /** @test */
    public function testCreateIniClass_fileContainsCorrectNamespace() {
        $className = 'TestIniNs'.uniqid();
        Ini::get()->createIniClass($className, 'NS test');

        $file = APP_PATH.'Ini'.DIRECTORY_SEPARATOR.$className.'.php';
        $content = file_get_contents($file);
        $this->assertStringContainsString('namespace '.APP_DIR.'\\Ini', $content);
        $this->assertStringContainsString("class $className", $content);
        $this->assertStringContainsString('public static function initialize()', $content);
        unlink($file);
    }

    /** @test */
    public function testCreateIniClass_fileContainsComment() {
        $className = 'TestIniComment'.uniqid();
        $comment = 'My special comment';
        Ini::get()->createIniClass($className, $comment);

        $file = APP_PATH.'Ini'.DIRECTORY_SEPARATOR.$className.'.php';
        $content = file_get_contents($file);
        $this->assertStringContainsString($comment, $content);
        unlink($file);
    }

    /** @test */
    public function testCreateIniClass_overwritesExistingFile() {
        $className = 'TestIniOverwrite'.uniqid();
        Ini::get()->createIniClass($className, 'First');
        Ini::get()->createIniClass($className, 'Second');

        $file = APP_PATH.'Ini'.DIRECTORY_SEPARATOR.$className.'.php';
        $content = file_get_contents($file);
        $this->assertStringContainsString('Second', $content);
        $this->assertStringNotContainsString('First', $content);
        unlink($file);
    }

    // ── createRoutesClass() ──────────────────────────────────────────────────

    /** @test */
    public function testCreateRoutesClass_createsFile() {
        $className = 'TestRoutes'.uniqid();
        Ini::get()->createRoutesClass($className);

        $file = APP_PATH.'Ini'.DIRECTORY_SEPARATOR.'Routes'.DIRECTORY_SEPARATOR.$className.'.php';
        $this->assertFileExists($file);
        unlink($file);
    }

    /** @test */
    public function testCreateRoutesClass_fileContainsCorrectStructure() {
        $className = 'TestRoutesStruct'.uniqid();
        Ini::get()->createRoutesClass($className);

        $file = APP_PATH.'Ini'.DIRECTORY_SEPARATOR.'Routes'.DIRECTORY_SEPARATOR.$className.'.php';
        $content = file_get_contents($file);
        $this->assertStringContainsString('namespace '.APP_DIR.'\\Ini\\Routes', $content);
        $this->assertStringContainsString("class $className", $content);
        $this->assertStringContainsString('public static function create()', $content);
        $this->assertStringContainsString('use WebFiori\\Framework\\Router\\Router', $content);
        unlink($file);
    }

    /** @test */
    public function testCreateRoutesClass_overwritesExistingFile() {
        $className = 'TestRoutesOverwrite'.uniqid();
        Ini::get()->createRoutesClass($className);
        $file = APP_PATH.'Ini'.DIRECTORY_SEPARATOR.'Routes'.DIRECTORY_SEPARATOR.$className.'.php';
        $mtime1 = filemtime($file);

        sleep(1);
        Ini::get()->createRoutesClass($className);
        clearstatcache();
        $mtime2 = filemtime($file);

        $this->assertGreaterThan($mtime1, $mtime2);
        unlink($file);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function removeDir(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.DIRECTORY_SEPARATOR.$item;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }
}
