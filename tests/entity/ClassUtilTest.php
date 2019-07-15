<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Util;
/**
 * A unit test class for testing the class webfiori/entity/Util.
 *
 * @author Ibrahim
 */
class ClassUtilTest extends TestCase{
    /**
     * Testing the method Util::numericValue() with valid inputs.
     * @test
     */
    public function testNumericValue00() {
        $num00 = Util::numericValue('0');
        $this->assertEquals('integer', gettype($num00));
        $this->assertEquals(0, $num00);
        
        $num01 = Util::numericValue('1');
        $this->assertEquals('integer', gettype($num01));
        $this->assertEquals(1, $num01);
        
        $num02 = Util::numericValue('-1  ');
        $this->assertEquals('integer', gettype($num02));
        $this->assertEquals(-1, $num02);
        
        $num03 = Util::numericValue('  1.77654');
        $this->assertEquals('double', gettype($num03));
        $this->assertEquals(1.77654, $num03);
        
        $num04 = Util::numericValue('-31.77654  ');
        $this->assertEquals('double', gettype($num04));
        $this->assertEquals(-31.77654, $num04);
        
        $num05 = Util::numericValue('   7522467.75424789   ');
        $this->assertEquals('double', gettype($num05));
        $this->assertEquals(7522467.75424789, $num05);
        
        $num06 = Util::numericValue('6564323   ');
        $this->assertEquals('integer', gettype($num06));
        $this->assertEquals(6564323, $num06);
        
        $num07 = Util::numericValue('-6564323   ');
        $this->assertEquals('integer', gettype($num07));
        $this->assertEquals(-6564323, $num07);
    }
    /**
     * Testing the method Util::numericValue() with invalid inputs.
     * @test
     */
    public function testNumericValue01() {
        $num00 = Util::numericValue('A');
        $this->assertFalse($num00);
        
        $num01 = Util::numericValue('');
        $this->assertFalse($num01);
        
        $num02 = Util::numericValue('--4');
        $this->assertFalse($num02);
        
        $num03 = Util::numericValue('1.88.4');
        $this->assertFalse($num03);
        
        $num04 = Util::numericValue(null);
        $this->assertFalse($num04);
        
        $num05 = Util::numericValue(true);
        $this->assertFalse($num05);
        
        $num06 = Util::numericValue(new \Exception());
        $this->assertFalse($num06);
    }
    /**
     * Testing the method Util::reverse().
     * @test
     */
    public function testReverse00() {
        $this->assertEquals('', Util::reverse(null));
        $this->assertEquals('1', Util::reverse(true));
        $this->assertEquals('', Util::reverse(false));
        $this->assertEquals('0987654321', Util::reverse(1234567890));
        $this->assertEquals('!dlroW olleH', Util::reverse('Hello World!'));
        $this->assertEquals('!dlroW olleH    ', Util::reverse('    Hello World!'));
        $this->assertEquals(' H      ', Util::reverse('      H '));
    }
    /**
     * Testing the method Util::reverse() using Arabic text.
     * @test
     */
    public function testReverse01() {
        if(function_exists('mb_strlen')){
            $this->assertEquals('ًالهأ', Util::reverse('أهلاً'));
        }
    }
    /**
     * @test
     */
    public function testToBinaryString00() {
        $this->assertFalse(Util::binaryString(''));
    }
    /**
     * @test
     */
    public function testToBinaryString01() {
        $this->assertFalse(Util::binaryString('1'));
    }
    /**
     * @test
     */
    public function testToBinaryString02() {
        $this->assertFalse(Util::binaryString(-1));
    }
    /**
     * @test
     */
    public function testToBinaryString03() {
        $this->assertEquals('0',Util::binaryString(0));
    }
    /**
     * @test
     */
    public function testToBinaryString04() {
        $this->assertEquals('1',Util::binaryString(1));
    }
    /**
     * @test
     */
    public function testToBinaryString05() {
        $this->assertEquals('10',Util::binaryString(2));
    }
    /**
     * @test
     */
    public function testToBinaryString06() {
        $this->assertEquals('11',Util::binaryString(3));
    }
    /**
     * @test
     */
    public function testToBinaryString07() {
        $this->assertEquals('1000',Util::binaryString(8));
    }
    /**
     * @test
     */
    public function testToBinaryString08() {
        $this->assertEquals('10000',Util::binaryString(16));
    }
    /**
     * @test
     */
    public function testToBinaryString09() {
        $this->assertEquals('11110',Util::binaryString(30));
    }
    /**
     * @test
     */
    public function testIsUpper() {
        $this->assertTrue(Util::isUpper('A'));
        $this->assertFalse(Util::isUpper('a'));
        $this->assertFalse(Util::isUpper('أ'));
    }
    /**
     * @test
     */
    public function testFilterScript00() {
        $text = '<? echo "Hello World!"';
        $this->assertEquals('&lt;? echo "Hello World!"', Util::filterScripts($text));
    }
    /**
     * @test
     */
    public function testFilterScript01() {
        $text = '<?php echo "Hello World!"';
        $this->assertEquals('&lt;?php echo "Hello World!"', Util::filterScripts($text));
    }
    /**
     * @test
     */
    public function testFilterScript02() {
        $text = '<script>alert("hello world!")</script>';
        $this->assertEquals('&lt;script&gt;alert("hello world!")&lt;/script&gt;', Util::filterScripts($text));
    }
}
