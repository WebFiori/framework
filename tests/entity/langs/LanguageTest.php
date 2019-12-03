<?php
namespace webfiori\tests\entity;
use webfiori\entity\langs\Language;
use PHPUnit\Framework\TestCase;
use webfiori\entity\File;
/**
 * Description of LanguageTest
 *
 * @author Eng.Ibrahim
 */
class LanguageTest extends TestCase{
    /**
     * Testing the constructor of the class 'Language' without using any parameters.
     * @test
     */
    public function testConstructor00() {
        $this->assertEquals(0,count(Language::getLoadedLangs()));
        $lang = new Language();
        $this->assertEquals(1,count(Language::getLoadedLangs()));
        $this->assertEquals('XX',$lang->getCode());
        $this->assertEquals('ltr',$lang->getWritingDir());
        $this->assertTrue($lang->isLoaded());
        $this->assertEquals(array(
            'code'=>'XX',
            'dir'=>'ltr'
        ),$lang->getLanguageVars());
        $this->assertTrue(Language::unloadTranslation('XX'));
    }
    /**
     * Testing the constructor of the class 'Language' using empty language 
     * code and writing direction and setting 'load' parameter to false.
     * @test
     */
    public function testConstructor01() {
        $this->assertEquals(0,count(Language::getLoadedLangs()));
        $lang = new Language('','',false);
        $this->assertEquals(0,count(Language::getLoadedLangs()));
        $this->assertEquals('XX',$lang->getCode());
        $this->assertEquals('ltr',$lang->getWritingDir());
        $this->assertFalse($lang->isLoaded());
        $this->assertEquals(array(
            'code'=>'XX',
            'dir'=>'ltr'
        ),$lang->getLanguageVars());
        $this->assertFalse(Language::unloadTranslation('XX'));
    }
    /**
     * Testing the constructor of the class 'Language' using 'ENU' as 
     * a language code and 'RtL' as writing direction.
     * @test
     */
    public function testConstructor02() {
        $this->assertEquals(0,count(Language::getLoadedLangs()));
        $lang = new Language('RtL','ENU',false);
        $this->assertEquals(0,count(Language::getLoadedLangs()));
        $this->assertEquals('XX',$lang->getCode());
        $this->assertEquals('rtl',$lang->getWritingDir());
        $this->assertFalse($lang->isLoaded());
        $this->assertEquals(array(
            'code'=>'XX',
            'dir'=>'rtl'
        ),$lang->getLanguageVars());
        $this->assertFalse(Language::unloadTranslation('ENU'));
    }
    /**
     * Testing the constructor of the class 'Language' using 'fR' as 
     * a language code and 'lTr' as writing direction.
     * @test
     */
    public function testConstructor03() {
        $this->assertEquals(0,count(Language::getLoadedLangs()));
        $lang = new Language('lTr','fR',false);
        $this->assertEquals(0,count(Language::getLoadedLangs()));
        $this->assertEquals('FR',$lang->getCode());
        $this->assertEquals('ltr',$lang->getWritingDir());
        $this->assertFalse($lang->isLoaded());
        $this->assertEquals(array(
            'code'=>'FR',
            'dir'=>'ltr'
        ),$lang->getLanguageVars());
        $this->assertFalse(Language::unloadTranslation('FR'));
    }
    /**
     * Try to load a language which does not have a translation file.
     * @test
     */
    public function testLoadTranslation00() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No language class was found for the language \'GB\'.');
        Language::loadTranslation('GB');
    }
    /**
     * Try to load a language which does have translation file but with no 
     * object which is a sub-class of the class 'Language'.
     * @test
     */
    public function testLoadTranslation01() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No language class was found for the language \'CA\'.');
        Language::loadTranslation('CA');
    }
    /**
     * Try to load a language which does have translation file but with no 
     * object which is a sub-class of the class 'Language'.
     * @test
     */
    public function testLoadTranslation02() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A language class for the language \'FR\' was found. But it is not a sub class of \'Language\'.');
        Language::loadTranslation('fr');
    }
    /**
     * Try to load a language which does have translation file and has an 
     * object which is a sub-class of the class 'Language'. But it is not set 
     * to be loaded.
     * @test
     */
    public function testLoadTranslation03() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The translation file was found. But no object of type \'Language\' is stored. Make sure that the parameter '
                                . '$addtoLoadedAfterCreate is set to true when creating the language object.');
        Language::loadTranslation('Jp');
    }
    /**
     * Load Arabic and English translation files.
     * @test
     */
    public function testLoadTranslation04() {
        $lang = Language::loadTranslation('Ar');
        $this->assertTrue($lang instanceof Language);
        $this->assertEquals('AR',$lang->getCode());
        $this->assertEquals('rtl',$lang->getWritingDir());
        $this->assertEquals(1,count(Language::getLoadedLangs()));
        
        $lang2 = Language::loadTranslation('en');
        $this->assertTrue($lang2 instanceof Language);
        $this->assertEquals('EN',$lang2->getCode());
        $this->assertEquals('ltr',$lang2->getWritingDir());
        $this->assertEquals(2,count(Language::getLoadedLangs()));
        
        $lang3 = Language::loadTranslation('en  ');
        $this->assertEquals(2,count(Language::getLoadedLangs()));
        $this->assertTrue($lang3 === $lang2);
    }
    /**
     * Testing the method Language::unloadTransaltion()
     * @test
     * @depends testLoadTranslation04
     */
    public function testUnloadTranslation00() {
        $this->assertFalse(Language::unloadTranslation('arx'));
        $this->assertEquals(2,count(Language::getLoadedLangs()));
        $this->assertTrue(Language::unloadTranslation('ar '));
        $this->assertEquals(1,count(Language::getLoadedLangs()));
        $this->assertFalse(Language::unloadTranslation('AR'));
        $this->assertTrue(Language::unloadTranslation(' En'));
        $this->assertEquals(0,count(Language::getLoadedLangs()));
    }
    /**
     * Testing the method 'Language::setCode()' for new instance which is 
     * not loaded.
     * @test
     */
    public function testSetCode00() {
        $lang = new Language('','',false);
        $this->assertFalse($lang->setCode(''));
        $this->assertFalse($lang->setCode('z'));
        $this->assertTrue($lang->setCode('zk'));
        $this->assertFalse($lang->setCode('zkf'));
        $this->assertEquals('ZK',$lang->getCode());
        $this->assertFalse($lang->setCode('1F'));
    }
    /**
     * Testing the method 'Language::setCode()' for a loaded translation.
     * @test
     */
    public function testSetCode01() {
        $lang = Language::loadTranslation('AR');
        $this->assertFalse($lang->setCode(''));
        $this->assertFalse($lang->setCode('z'));
        $this->assertTrue($lang->setCode('zk'));
        $this->assertFalse($lang->setCode('zkf'));
        $this->assertEquals('ZK',$lang->getCode());
        $this->assertFalse($lang->setCode('1F'));
        $this->assertFalse(Language::unloadTranslation('AR'));
        $this->assertTrue(Language::unloadTranslation('zk'));
    }
    /**
     * @test
     */
    public function testSet00() {
        $lang = Language::loadTranslation('en');
        $this->assertEquals('Please wait a moment...',$lang->get('general/status/wait'));
        $this->assertTrue($lang->set('general/status', 'wait', 'Wait a sec...'));
        $this->assertEquals('Wait a sec...',$lang->get('general/status/wait'));
    }
    /**
     * @test
     */
    public function testSet01() {
        $lang = Language::loadTranslation('en');
        $this->assertFalse($lang->set('', '', ''));
    }
    /**
     * @test
     */
    public function testSet02() {
        $lang = Language::loadTranslation('en');
        $this->assertFalse($lang->set('general/xcderf', 'a-var', 'mmm'));
    }
    /**
     * @test
     */
    public function testSet03() {
        $lang = Language::loadTranslation('en');
        $this->assertEquals('array', gettype($lang->get('general')));
        $this->assertTrue($lang->set('general', 'a-var', 'mmm'));
        $this->assertEquals('mmm',$lang->get('general/a-var'));
    }
    /**
     * Testing the method Language::get() with non-exiting language variable.
     * @test
     */
    public function testGet00() {
        $lang = Language::loadTranslation('ar');
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
    public function testCreateAndSet00() {
        $lang = Language::loadTranslation('en');
        $lang->createAndSet(' general/sub/sub2/all-new/', array(
            'Nice','Work','hello'=>'Hello World!','ok'=>'Okay'
        ));
        $vars = $lang->get('general/sub/sub2/all-new');
        $this->assertEquals(array(
            '0'=>'Nice',
            '1'=>'Work',
            'hello'=>'Hello World!',
            'ok'=>'Okay'
        ),$vars);
        $this->assertEquals('Hello World!',$lang->get('general/sub/sub2/all-new/hello'));
        $this->assertEquals('Nice',$lang->get('general/sub/sub2/all-new/0'));
    }
}






