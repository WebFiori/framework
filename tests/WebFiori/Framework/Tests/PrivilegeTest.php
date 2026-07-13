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
     */
    public function testToJson00() {
        $pr = new Privilege('  Valid_ID_55  ','Valid Name');
        $j = $pr->toJSON();
        $j->setPropsStyle('camel');
        $this->assertEquals('{"privilegeId":"Valid_ID_55","name":"Valid Name"}',$j.'');
    }
    /**
     * @test
     * Tests that a permission ID with dots is accepted and stored correctly.
     * @see https://github.com/webfiori/framework/issues/404
     */
    public function testSetIDWithDots() {
        $pr = new Privilege('orders.create', 'Create Orders');
        $this->assertEquals('orders.create', $pr->getID());
        $this->assertEquals('Create Orders', $pr->getName());
    }
    /**
     * @test
     * Tests that a permission ID with dashes is accepted and stored correctly.
     * @see https://github.com/webfiori/framework/issues/404
     */
    public function testSetIDWithDashes() {
        $pr = new Privilege('orders-create', 'Create Orders');
        $this->assertEquals('orders-create', $pr->getID());
        $this->assertEquals('Create Orders', $pr->getName());
    }
    /**
     * @test
     * Tests that a permission ID with dots and dashes combined is accepted.
     * @see https://github.com/webfiori/framework/issues/404
     */
    public function testSetIDWithDotsAndDashes() {
        $pr = new Privilege('app.orders-create', 'Create Orders');
        $this->assertEquals('app.orders-create', $pr->getID());
        $this->assertEquals('Create Orders', $pr->getName());
    }
    /**
     * @test
     * Tests that multiple permissions with dotted IDs are distinguishable
     * and don't collapse to the same default ID.
     * @see https://github.com/webfiori/framework/issues/404
     */
    public function testMultipleDottedIDsAreUnique() {
        $pr1 = new Privilege('orders.create', 'Create Orders');
        $pr2 = new Privilege('orders.view', 'View Orders');
        $pr3 = new Privilege('orders.cancel', 'Cancel Orders');

        $this->assertEquals('orders.create', $pr1->getID());
        $this->assertEquals('orders.view', $pr2->getID());
        $this->assertEquals('orders.cancel', $pr3->getID());

        // Ensure they are all different
        $this->assertNotEquals($pr1->getID(), $pr2->getID());
        $this->assertNotEquals($pr2->getID(), $pr3->getID());
        $this->assertNotEquals($pr1->getID(), $pr3->getID());
    }
    /**
     * @test
     * Tests that setID() with dots returns true indicating success.
     * @see https://github.com/webfiori/framework/issues/404
     */
    public function testSetIDWithDotsReturnsTrue() {
        $pr = new Privilege();
        $this->assertTrue($pr->setID('orders.create'));
        $this->assertEquals('orders.create', $pr->getID());
    }
    /**
     * @test
     * Tests that setID() with dashes returns true indicating success.
     * @see https://github.com/webfiori/framework/issues/404
     */
    public function testSetIDWithDashesReturnsTrue() {
        $pr = new Privilege();
        $this->assertTrue($pr->setID('orders-create'));
        $this->assertEquals('orders-create', $pr->getID());
    }
}
