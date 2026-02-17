<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;

/**
 * Test cases for creating attribute-based table schemas.
 * 
 * @author Ibrahim
 */
class CreateAttributeTableTest extends CLITestCase {
    
    protected function tearDown(): void {
        $this->cleanupInfrastructure();
        parent::tearDown();
    }
    
    private function cleanupInfrastructure(): void {
        $dir = APP_PATH . 'Infrastructure' . DS . 'Schema';
        if (is_dir($dir)) {
            foreach (glob($dir . DS . '*.php') as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * @test
     */
    public function testCreateAttributeTable() {
        $output = $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'table-attributes'
        ], [
            "\n",  // namespace (use default)
            'UsersTable',  // class name
            'users',  // table name
            'id',  // column name
            '0',  // type: INT
            '11',  // size
            'y',  // is primary
            'y',  // auto increment
            'n',  // not nullable
            'name',  // column name
            '1',  // type: VARCHAR
            '100',  // size
            'n',  // not primary
            'n',  // not nullable
            'email',  // column name
            '1',  // type: VARCHAR
            '150',  // size
            'n',  // not primary
            'n',  // not nullable
            "\n"  // finish
        ]);
        
        $this->assertEquals(0, $this->getExitCode());
        
        $filePath = APP_PATH . 'Infrastructure' . DS . 'Schema' . DS . 'UsersTable.php';
        $this->assertTrue(file_exists($filePath), 'Table schema file should exist');
        
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('namespace App\\Infrastructure\\Schema', $content);
        $this->assertStringContainsString('use WebFiori\\Database\\Attributes\\Table', $content);
        $this->assertStringContainsString('use WebFiori\\Database\\Attributes\\Column', $content);
        $this->assertStringContainsString('#[Table(name: \'users\')]', $content);
        $this->assertStringContainsString('#[Column(name: \'id\'', $content);
        $this->assertStringContainsString('primary: true', $content);
        $this->assertStringContainsString('autoIncrement: true', $content);
        $this->assertStringContainsString('#[Column(name: \'name\'', $content);
        $this->assertStringContainsString('#[Column(name: \'email\'', $content);
        $this->assertStringContainsString('class UsersTable', $content);
    }
    
    /**
     * @test
     */
    public function testCreateAttributeTableWithDefaults() {
        $output = $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'table-attributes',
            '--defaults' => ''
        ], [
            'ProductsTable',
            'products'
        ]);
        
        $this->assertEquals(0, $this->getExitCode());
        
        $filePath = APP_PATH . 'Infrastructure' . DS . 'Schema' . DS . 'ProductsTable.php';
        $this->assertTrue(file_exists($filePath));
        
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('class ProductsTable', $content);
        $this->assertStringContainsString('#[Table(name: \'products\')]', $content);
    }
}
