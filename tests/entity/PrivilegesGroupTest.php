<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\PrivilegesGroup;

/**
 * A test class for testing the class 'webfiori\entity\File'.
 *
 * @author Ibrahim
 */
class PrivilegesGroupTest extends TestCase{
    /**
     * @test
     */
    public function testConstructor00() {
        $group = new PrivilegesGroup();
        $this->assertEquals('GROUP',$group->getID());
        $this->assertEquals('G_NAME',$group->getName());
    }
    /**
     * @test
     */
    public function testConstructor01() {
        $group = new PrivilegesGroup('Invalid ID','Valid Name');
        $this->assertEquals('GROUP',$group->getID());
        $this->assertEquals('Valid Name',$group->getName());
    }
    /**
     * @test
     */
    public function testConstructor02() {
        $group = new PrivilegesGroup(' valid_ID','Valid Name');
        $this->assertEquals('valid_ID',$group->getID());
        $this->assertEquals('Valid Name',$group->getName());
        return $group;
    }
    /**
     * @test
     */
    public function testConstructor03() {
        $group = new PrivilegesGroup(1,2);
        $this->assertSame('1',$group->getID());
        $this->assertSame('2',$group->getName());
    }
    /**
     * @test
     */
    public function testConstructor04() {
        $group = new PrivilegesGroup('','');
        $this->assertEquals('GROUP',$group->getID());
        $this->assertEquals('G_NAME',$group->getName());
    }
    /**
     * @test
     */
    public function testSetParentGroup00() {
        $child = new PrivilegesGroup('CH_GROUP_1', 'Child Group #1');
        $parentGroup = new PrivilegesGroup('PARENT_1', 'Parent Group #1');
        $this->assertFalse($child->setParentGroup($child));
        $this->assertTrue($child->setParentGroup($parentGroup));
        $this->assertSame($parentGroup,$child->getParentGroup());
        $this->assertEquals(1,count($child->getParentGroup()->childGroups()));
        $this->assertSame($child,$parentGroup->childGroups()[0]);
    }
    /**
     * 
     * @param PrivilegesGroup $group
     * @depends testConstructor02
     * @test
     */
    public function testToJson00($group) {
        $this->assertEquals('{"group-id":"valid_ID", "parent-group-id":null, "name":"Valid Name", "privileges":[], "child-groups":[]}',$group->toJSON().'');
    }
}
