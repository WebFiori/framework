<?php
namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Util as U;
/**
 * A unit test class for testing the class WebFiori/entity/Util.
 *
 * @author Ibrahim
 */
class ClassUtilTest extends TestCase {
    /**
     * @test
     */
    public function testFilterScript00() {
        $text = '<? echo "Hello World!"';
        $this->assertEquals('&lt;? echo "Hello World!"', U::filterScripts($text));
    }
    /**
     * @test
     */
    public function testFilterScript01() {
        $text = '<?php echo "Hello World!"';
        $this->assertEquals('&lt;?php echo "Hello World!"', U::filterScripts($text));
    }
    /**
     * @test
     */
    public function testFilterScript02() {
        $text = '<script>alert("hello world!")</script>';
        $this->assertEquals('&lt;script&gt;alert("hello world!")&lt;/script&gt;', U::filterScripts($text));
    }
    /**
     * @test
     */
    public function testIsUpper() {
        $this->assertTrue(U::isUpper('A'));
        $this->assertFalse(U::isUpper('a'));
        $this->assertFalse(U::isUpper('أ'));
    }
    /**
     * Testing the method Util::numericValue() with valid inputs.
     * @test
     */
    public function testNumericValue00() {
        $num00 = U::numericValue('0');
        $this->assertEquals('integer', gettype($num00));
        $this->assertEquals(0, $num00);

        $num01 = U::numericValue('1');
        $this->assertEquals('integer', gettype($num01));
        $this->assertEquals(1, $num01);

        $num02 = U::numericValue('-1  ');
        $this->assertEquals('integer', gettype($num02));
        $this->assertEquals(-1, $num02);

        $num03 = U::numericValue('  1.77654');
        $this->assertEquals('double', gettype($num03));
        $this->assertEquals(1.77654, $num03);

        $num04 = U::numericValue('-31.77654  ');
        $this->assertEquals('double', gettype($num04));
        $this->assertEquals(-31.77654, $num04);

        $num05 = U::numericValue('   7522467.75424789   ');
        $this->assertEquals('double', gettype($num05));
        $this->assertEquals(7522467.75424789, $num05);

        $num06 = U::numericValue('6564323   ');
        $this->assertEquals('integer', gettype($num06));
        $this->assertEquals(6564323, $num06);

        $num07 = U::numericValue('-6564323   ');
        $this->assertEquals('integer', gettype($num07));
        $this->assertEquals(-6564323, $num07);
    }
    /**
     * Testing the method Util::numericValue() with invalid inputs.
     * @test
     */
    public function testNumericValue01() {
        $num00 = U::numericValue('A');
        $this->assertFalse($num00);

        $num01 = U::numericValue('');
        $this->assertFalse($num01);

        $num02 = U::numericValue('--4');
        $this->assertFalse($num02);

        $num03 = U::numericValue('1.88.4');
        $this->assertFalse($num03);
    }
    /**
     * Testing the method Util::reverse().
     * @test
     */
    public function testReverse00() {
        $this->assertEquals('', U::reverse(null));
        $this->assertEquals('1', U::reverse(true));
        $this->assertEquals('', U::reverse(false));
        $this->assertEquals('0987654321', U::reverse(1234567890));
        $this->assertEquals('!dlroW olleH', U::reverse('Hello World!'));
        $this->assertEquals('!dlroW olleH    ', U::reverse('    Hello World!'));
        $this->assertEquals(' H      ', U::reverse('      H '));
    }
    /**
     * Testing the method Util::reverse() using Arabic text.
     * @test
     */
    public function testReverse01() {
        if (function_exists('mb_strlen')) {
            $this->assertEquals('ًالهأ', U::reverse('أهلاً'));
        }
    }
    /**
     * @test
     */
    public function testToBinaryString00() {
        $this->assertFalse(U::binaryString(''));
    }
    /**
     * @test
     */
    public function testToBinaryString01() {
        $this->assertFalse(U::binaryString('1'));
    }
    /**
     * @test
     */
    public function testToBinaryString02() {
        $this->assertFalse(U::binaryString(-1));
    }
    /**
     * @test
     */
    public function testToBinaryString03() {
        $this->assertEquals('0',U::binaryString(0));
    }
    /**
     * @test
     */
    public function testToBinaryString04() {
        $this->assertEquals('1',U::binaryString(1));
    }
    /**
     * @test
     */
    public function testToBinaryString05() {
        $this->assertEquals('10',U::binaryString(2));
    }
    /**
     * @test
     */
    public function testToBinaryString06() {
        $this->assertEquals('11',U::binaryString(3));
    }
    /**
     * @test
     */
    public function testToBinaryString07() {
        $this->assertEquals('1000',U::binaryString(8));
    }
    /**
     * @test
     */
    public function testToBinaryString08() {
        $this->assertEquals('10000',U::binaryString(16));
    }
    /**
     * @test
     */
    public function testToBinaryString09() {
        $this->assertEquals('11110',U::binaryString(30));
    }
}
