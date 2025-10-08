<?php
namespace WebFiori\Tests\Entity;

use Exception;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Lang;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Session\DefaultSessionStorage;
/**
 * Description of LanguageTest
 *
 * @author Eng.Ibrahim
 */
class LanguageTest extends TestCase {
    /**
     * @test
     */
    public function testActive00() {
        Lang::reset();
        $this->assertNull(Lang::getActive());
        Lang::loadTranslation('EN');
        $active = Lang::getActive();
        $this->assertEquals('EN', $active->getCode());
        $this->assertEquals('ltr', $active->getWritingDir());
    }
    /**
     * Testing the constructor of the class 'Lang' without using any parameters.
     * @test
     */
    public function testConstructor00() {
        Lang::reset();
        $this->assertEquals(0,count(Lang::getLoadedLangs()));
        $lang = new Lang();
        $this->assertEquals(1,count(Lang::getLoadedLangs()));
        $this->assertEquals('XX',$lang->getCode());
        $this->assertEquals('ltr',$lang->getWritingDir());
        $this->assertTrue($lang->isLoaded());
        $this->assertEquals([
            'code' => 'XX',
            'dir' => 'ltr'
        ],$lang->getLanguageVars());
        $this->assertTrue(Lang::unloadTranslation('XX'));
    }
    /**
     * Testing the constructor of the class 'Lang' using empty language
     * code and writing direction and setting 'load' parameter to false.
     * @test
     */
    public function testConstructor01() {
        $this->assertEquals(0,count(Lang::getLoadedLangs()));
        $lang = new Lang('','',false);
        $this->assertEquals(0,count(Lang::getLoadedLangs()));
        $this->assertEquals('XX',$lang->getCode());
        $this->assertEquals('ltr',$lang->getWritingDir());
        $this->assertFalse($lang->isLoaded());
        $this->assertEquals([
            'code' => 'XX',
            'dir' => 'ltr'
        ],$lang->getLanguageVars());
        $this->assertFalse(Lang::unloadTranslation('XX'));
    }
    /**
     * Testing the constructor of the class 'Lang' using 'ENU' as
     * a language code and 'RtL' as writing direction.
     * @test
     */
    public function testConstructor02() {
        $this->assertEquals(0,count(Lang::getLoadedLangs()));
        $lang = new Lang('RtL','ENU',false);
        $this->assertEquals(0,count(Lang::getLoadedLangs()));
        $this->assertEquals('XX',$lang->getCode());
        $this->assertEquals('rtl',$lang->getWritingDir());
        $this->assertFalse($lang->isLoaded());
        $this->assertEquals([
            'code' => 'XX',
            'dir' => 'rtl'
        ],$lang->getLanguageVars());
        $this->assertFalse(Lang::unloadTranslation('ENU'));
    }
    /**
     * Testing the constructor of the class 'Lang' using 'fR' as
     * a language code and 'lTr' as writing direction.
     * @test
     */
    public function testConstructor03() {
        $this->assertEquals(0,count(Lang::getLoadedLangs()));
        $lang = new Lang('lTr','fR',false);
        $this->assertEquals(0,count(Lang::getLoadedLangs()));
        $this->assertEquals('FR',$lang->getCode());
        $this->assertEquals('ltr',$lang->getWritingDir());
        $this->assertFalse($lang->isLoaded());
        $this->assertEquals([
            'code' => 'FR',
            'dir' => 'ltr'
        ],$lang->getLanguageVars());
        $this->assertFalse(Lang::unloadTranslation('FR'));
    }
    /**
     * @test
     */
    public function testCreateAndSet00() {
        $lang = Lang::loadTranslation('en');
        $lang->createAndSet(' general/sub/sub2/all-new/', [
            'Nice','Work','hello' => 'Hello World!','ok' => 'Okay'
        ]);
        $vars = $lang->get('general/sub/sub2/all-new');
        $this->assertEquals([
            '0' => 'Nice',
            '1' => 'Work',
            'hello' => 'Hello World!',
            'ok' => 'Okay'
        ],$vars);
        $this->assertEquals('Hello World!',$lang->get('general/sub/sub2/all-new/hello'));
        $this->assertEquals('Nice',$lang->get('general/sub/sub2/all-new/0'));
    }
    /**
     * Testing the method Language::get() with non-exiting language variable.
     * @test
     */
    public function testGet00() {
        $lang = Lang::loadTranslation('ar');
        $lang->createDirectory('general');
        $var = $lang->get('   this/does/not/exist/');
        $this->assertEquals('this/does/not/exist',$var);
        $var2 = $lang->get('general/not-exist');
        $this->assertEquals('general/not-exist',$var2);

        $var3 = $lang->get('general');
        $this->assertEquals('array', gettype($var3));
    }
    /**
     * @test
     */
    public function testGet01() {
        $lang = Lang::loadTranslation('ar');
        $lang->createDirectory('general');
        $var = $lang->get('   this.does.not.exist.');
        $this->assertEquals('this/does/not/exist',$var);
        $var2 = $lang->get('general.not-exist');
        $this->assertEquals('general/not-exist',$var2);

        $var3 = $lang->get('general');
        $this->assertEquals('array', gettype($var3));
    }
    /**
     * @test
     */
    public function testGetLabel00() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No language class was found for the language \'GB\'.');
        Lang::getLabel('general.ok','GB');
    }
    /**
     * @test
     */
    public function testGetLabel01() {
        Lang::reset();
        $this->assertNull(Lang::getActive());
        $this->assertEquals('general/action/print', Lang::getLabel('general/action/print','EN'));
        $this->assertEquals('EN', Lang::getActive()->getCode());
        Lang::getActive()->set('general.action', 'print', 'Print Report');
        $_POST['lang'] = 'EN';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('EN', Lang::getActive()->getCode());
        $this->assertEquals('Print Report', Lang::getLabel('general/action/print'));
        $this->assertEquals('general/action/print', Lang::getLabel('general/action/print','AR'));
        $this->assertEquals('Print Report', Lang::getLabel('general/action/print', 'EN'));
        $this->assertEquals('general/action/print', Lang::getLabel('general.action.print','AR'));
        Lang::getActive()->set('general.action', 'print', 'طباعة التقرير');
        $this->assertEquals('طباعة التقرير', Lang::getLabel('general.action.print','AR'));
        $this->assertEquals('Print Report', Lang::getLabel('general.action.print'));
    }
    /**
     * @test
     * @depends testGetLabel01
     */
    public function testGetLabel02() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        SessionsManager::setStorage(new DefaultSessionStorage());
        SessionsManager::start('new-x-session');
        $_POST['lang'] = 'ar';
        $this->assertEquals('طباعة التقرير', Lang::getLabel('general.action.print','AR'));
        $_POST['lang'] = 'en';
        $this->assertEquals('Print Report', Lang::getLabel('general.action.print'));
        Lang::reset();
    }
    /**
     * Try to load a language which does not have a translation file.
     * @test
     */
    public function testLoadTranslation00() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No language class was found for the language \'GB\'.');
        Lang::loadTranslation('GB');
    }
    /**
     * Try to load a language which does have translation file but with no
     * object which is a sub-class of the class 'Lang'.
     * @test
     */
    public function testLoadTranslation01() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No language class was found for the language \'CA\'.');
        Lang::loadTranslation('CA');
    }
    /**
     * Try to load a language which does have translation file but with no
     * object which is a sub-class of the class 'Lang'.
     * @test
     */
    public function testLoadTranslation02() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('A language class for the language \'FR\' was found. But it is not a sub class of \'WebFiori\Framework\Lang\'.');
        Lang::loadTranslation('fr');
    }
    /**
     * Try to load a language which does have translation file and has an
     * object which is a sub-class of the class 'Lang'. But it is not set
     * to be loaded.
     * @test
     */
    public function testLoadTranslation03() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The translation file was found. But no object of type \'WebFiori\Framework\Lang\' is stored. Make sure that the parameter '
                                .'$addtoLoadedAfterCreate is set to true when creating the language object.');
        Lang::loadTranslation('Jp');
    }
    /**
     * Load Arabic and English translation files.
     * @test
     */
    public function testLoadTranslation04() {
        Lang::reset();
        $lang = Lang::loadTranslation('Ar');
        $this->assertTrue($lang instanceof Lang);
        $this->assertEquals('AR',$lang->getCode());
        $this->assertEquals('rtl',$lang->getWritingDir());
        $this->assertEquals(1,count(Lang::getLoadedLangs()));

        $lang2 = Lang::loadTranslation('en');
        $this->assertTrue($lang2 instanceof Lang);
        $this->assertEquals('EN',$lang2->getCode());
        $this->assertEquals('ltr',$lang2->getWritingDir());
        $this->assertEquals(2,count(Lang::getLoadedLangs()));

        $lang3 = Lang::loadTranslation('en  ');
        $this->assertEquals(2,count(Lang::getLoadedLangs()));
        $this->assertTrue($lang3 === $lang2);
    }
    /**
     * @test
     */
    public function testSet00() {
        $lang = Lang::loadTranslation('en');
        $lang->createAndSet('general/status', [
            'wait' => 'Please wait a moment...'
        ]);
        $this->assertEquals('Please wait a moment...',$lang->get('general/status/wait'));
        $lang->set('general/status', 'wait', 'Wait a sec...');
        $this->assertEquals('Wait a sec...',$lang->get('general/status/wait'));
    }
    /**
     * @test
     */
    public function testSet02() {
        $lang = Lang::loadTranslation('en');
        $lang->set('general/xcderf', 'a-var', 'mmm');
        $this->assertEquals('general/xcderf/a-var',$lang->get('general/xcderf/a-var'));
    }
    /**
     * @test
     */
    public function testSet03() {
        $lang = Lang::loadTranslation('en');
        $this->assertEquals('array', gettype($lang->get('general')));
        $lang->set('general', 'a-var', 'mmm');
        $this->assertEquals('mmm',$lang->get('general/a-var'));
    }
    /**
     * @test
     */
    public function testSet04() {
        $lang = Lang::loadTranslation('en');
        $this->assertEquals('a/var/not/exist', $lang->get('a/var/not/exist'));
        $lang->set('a/var/not', 'exist', 'No I Exist');
        $this->assertEquals('No I Exist', $lang->get('a/var/not/exist'));
    }
    /**
     * @test
     */
    public function testSet05() {
        $lang = Lang::loadTranslation('en');
        $lang->setMultiple('a/var', [
            'x' => 'Good',
            'y' => [
                'Z' => 'Super'
            ]
        ]);
        $this->assertEquals('Good', $lang->get('a/var/x'));
        $this->assertEquals('Super', $lang->get('a/var/y/Z'));
    }
    /**
     * @test
     */
    public function testSet06() {
        $lang = Lang::loadTranslation('en');
        $lang->createAndSet('general.new.status', [
            'wait' => 'Please wait a moment...'
        ]);
        $this->assertEquals('Please wait a moment...',$lang->get('general.new.status.wait'));
        $this->assertEquals('Please wait a moment...',$lang->get('general/new/status/wait'));
        $lang->set('general.new.status', 'wait', 'Wait a sec...');
        $this->assertEquals('Wait a sec...',$lang->get('general/new/status/wait'));
        $this->assertEquals('Wait a sec...',$lang->get('general.new.status/wait'));
    }
    /**
     * Testing the method 'Lang::setCode()' for new instance which is
     * not loaded.
     * @test
     */
    public function testSetCode00() {
        $lang = new Lang('','',false);
        $this->assertFalse($lang->setCode(''));
        $this->assertFalse($lang->setCode('z'));
        $this->assertTrue($lang->setCode('zk'));
        $this->assertFalse($lang->setCode('zkf'));
        $this->assertEquals('ZK',$lang->getCode());
        $this->assertFalse($lang->setCode('1F'));
    }
    /**
     * Testing the method 'Lang::setCode()' for a loaded translation.
     * @test
     * @depends testUnloadTranslation00
     */
    public function testSetCode01() {
        $lang = Lang::loadTranslation('AR');
        $this->assertFalse($lang->setCode(''));
        $this->assertFalse($lang->setCode('z'));
        $this->assertTrue($lang->setCode('zk'));
        $this->assertFalse($lang->setCode('zkf'));
        $this->assertEquals('ZK',$lang->getCode());
        $this->assertFalse($lang->setCode('1F'));
        $this->assertFalse(Lang::unloadTranslation('AR'));
        $this->assertTrue(Lang::unloadTranslation('zk'));
    }
    /**
     * Testing the method Lang::unloadTransaltion()
     * @test
     * @depends testLoadTranslation04
     */
    public function testUnloadTranslation00() {
        $this->assertEquals(2,count(Lang::getLoadedLangs()));
        $this->assertFalse(Lang::unloadTranslation('arx'));
        $this->assertEquals(2,count(Lang::getLoadedLangs()));
        $this->assertTrue(Lang::unloadTranslation('ar '));
        $this->assertEquals(1,count(Lang::getLoadedLangs()));
        $this->assertFalse(Lang::unloadTranslation('AR'));
        $this->assertTrue(Lang::unloadTranslation(' En'));
        $this->assertEquals(0,count(Lang::getLoadedLangs()));
    }
}
