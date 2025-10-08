<?php
namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Privilege;
/**
 * A test class for testing the class 'WebFiori\Framework\Privilege'.
 *
 * @author Ibrahim
 */
class PrivilegeTest extends TestCase {
    /**
     * @test
     */
    public function testConstructor00() {
        $pr = new Privilege();
        $this->assertEquals('PR',$pr->getID());
        $this->assertEquals('PR_NAME',$pr->getName());
    }
    /**
     * @test
     */
    public function testConstructor01() {
        $pr = new Privilege('Invalid ID','Valid Name');
        $this->assertEquals('PR',$pr->getID());
        $this->assertEquals('Valid Name',$pr->getName());
    }
    /**
     * @test
     */
    public function testConstructor02() {
        $pr = new Privilege('  Valid_ID_55  ','Valid Name');
        $this->assertEquals('Valid_ID_55',$pr->getID());
        $this->assertEquals('Valid Name',$pr->getName());

        return $pr;
    }
    /**
     * @test
     */
    public function testConstructor03() {
        $pr = new Privilege(1,2);
        $this->assertSame('1',$pr->getID());
        $this->assertSame('2',$pr->getName());

        return $pr;
    }
    /**
     * @test
     * @depends testConstructor02
     * @param Privilege $pr
     */
    public function testToJson00($pr) {
        $j = $pr->toJSON();
        $j->setPropsStyle('camel');
        $this->assertEquals('{"privilegeId":"Valid_ID_55","name":"Valid Name"}',$j.'');
    }
}
