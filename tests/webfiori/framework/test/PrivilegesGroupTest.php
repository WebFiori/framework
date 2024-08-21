<?php
namespace webfiori\framework\test;

use PHPUnit\Framework\TestCase;
use webfiori\framework\Privilege;
use webfiori\framework\PrivilegesGroup;

/**
 *
 * @author Ibrahim
 */
class PrivilegesGroupTest extends TestCase {
    /**
     * @test
     */
    public function testAddPrivilege01() {
        $g = new PrivilegesGroup();
        $pr = new Privilege();
        $this->assertTrue($g->addPrivilege($pr));
        $pr1 = new Privilege();
        $this->assertFalse($g->addPrivilege($pr1));
    }
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
    public function testHasPrivilege00() {
        $parent = new PrivilegesGroup('Parent');
        $child = new PrivilegesGroup('Child');
        $pr = new Privilege('Child_pr');
        $child->addPrivilege($pr);
        $child->setParentGroup($parent);
        $this->assertTrue($parent->hasPrivilege($pr));
        $this->assertFalse($parent->hasPrivilege($pr,false));
    }
    /**
     * @test
     */
    public function testHasPrivilege01() {
        $parent = new PrivilegesGroup('Parent');
        $child = new PrivilegesGroup('Child');
        $childOfChild = new PrivilegesGroup('Child_of_child');
        $pr = new Privilege('Child_pr');
        $childOfChild->addPrivilege($pr);
        $child->setParentGroup($parent);
        $childOfChild->setParentGroup($child);
        $this->assertTrue($parent->hasPrivilege($pr));
        $this->assertFalse($parent->hasPrivilege($pr,false));
    }
    /**
     * @test
     * @depends testSetParentGroup00
     * @param $gArr Description
     */
    public function testRemoveParentGroup00($gArr) {
        $this->assertTrue($gArr['child']->setParentGroup());
        $this->assertEquals(0,count($gArr['parent']->childGroups()));
        $this->assertNull($gArr['child']->getParentGroup());
    }

    /**
     * @test
     */
    public function testRemoveParentGroup01() {
        $child = new PrivilegesGroup('CH_GROUP_1', 'Child Group #1');
        $this->assertFalse($child->setParentGroup());
        
    }
    /**
     * @test
     */
    public function testSetID00() {
        $parent = new PrivilegesGroup('Parent');
        $child = new PrivilegesGroup('Child');
        $pr = new Privilege('Child_pr');
        $child->addPrivilege($pr);
        $child->setParentGroup($parent);
        $this->assertFalse($child->setID('Parent'));
        $this->assertEquals('Child',$child->getID());
    }
    /**
     * @test
     */
    public function testSetID01() {
        $parent = new PrivilegesGroup('Parent');
        $child = new PrivilegesGroup('Child');
        $childOfChild = new PrivilegesGroup('Child_of_child');
        $pr = new Privilege('Child_pr');
        $child->addPrivilege($pr);
        $child->setParentGroup($parent);
        $childOfChild->setParentGroup($child);
        $this->assertFalse($child->setID('Parent'));
        $this->assertEquals('Child',$child->getID());
        $this->assertFalse($childOfChild->setID('Parent'));
        $this->assertFalse($childOfChild->setID('Child'));
        $this->assertFalse($parent->setID('Child_of_child'));
    }
    /**
     * @test
     */
    public function testSetParentGroup00() {
        $child = new PrivilegesGroup('CH_GROUP_1', 'Child Group #1');
        $parentGroup = new PrivilegesGroup('PARENT_1', 'Parent Group #1');
        $this->assertFalse($parentGroup->setParentGroup(null));
        $this->assertFalse($child->setParentGroup($child));
        $this->assertTrue($child->setParentGroup($parentGroup));
        $this->assertSame($parentGroup,$child->getParentGroup());
        $this->assertEquals(1,count($child->getParentGroup()->childGroups()));
        $this->assertSame($child,$parentGroup->childGroups()[0]);

        return [
            'child' => $child,
            'parent' => $parentGroup
        ];
    }
    /**
     *
     * @param PrivilegesGroup $group
     * @depends testConstructor02
     * @test
     */
    public function testToJson00($group) {
        $j = $group->toJSON();
        $j->setPropsStyle('kebab');
        $this->assertEquals('{"group-id":"valid_ID","parent-group-id":null,"name":"Valid Name","privileges":[],"child-groups":[]}',$j.'');
    }
}
