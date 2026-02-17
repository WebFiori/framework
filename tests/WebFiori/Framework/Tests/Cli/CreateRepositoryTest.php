<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;

/**
 * Test cases for creating repository classes.
 * 
 * @author Ibrahim
 */
class CreateRepositoryTest extends CLITestCase {
    
    protected function tearDown(): void {
        $this->cleanupInfrastructure();
        parent::tearDown();
    }
    
    private function cleanupInfrastructure(): void {
        $dir = APP_PATH . 'Infrastructure' . DS . 'Repository';
        if (is_dir($dir)) {
            foreach (glob($dir . DS . '*.php') as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * @test
     */
    public function testCreateRepository() {
        $output = $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'repository'
        ], [
            "\n",  // namespace (use default)
            'UserRepository',  // class name
            'App\\Domain\\User',  // entity class
            'users',  // table name
            'id',  // id field
            'id',  // property name
            '0',  // type: int
            'name',  // property name
            '1',  // type: string
            'email',  // property name
            '1',  // type: string
            "\n"  // finish
        ]);
        
        $this->assertEquals(0, $this->getExitCode());
        
        $filePath = APP_PATH . 'Infrastructure' . DS . 'Repository' . DS . 'UserRepository.php';
        $this->assertTrue(file_exists($filePath), 'Repository file should exist');
        
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('namespace App\\Infrastructure\\Repository', $content);
        $this->assertStringContainsString('use WebFiori\\Database\\Repository\\AbstractRepository', $content);
        $this->assertStringContainsString('use App\\Domain\\User', $content);
        $this->assertStringContainsString('class UserRepository extends AbstractRepository', $content);
        $this->assertStringContainsString('function getTableName()', $content);
        $this->assertStringContainsString('return \'users\'', $content);
        $this->assertStringContainsString('function getIdField()', $content);
        $this->assertStringContainsString('return \'id\'', $content);
        $this->assertStringContainsString('function toEntity', $content);
        $this->assertStringContainsString('function toArray', $content);
        $this->assertStringContainsString('new User(', $content);
    }
    
    /**
     * @test
     */
    public function testCreateRepositoryWithDefaults() {
        $output = $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'repository',
            '--defaults' => ''
        ], [
            'ProductRepository',
            'App\\Domain\\Product',
            'products',
            'id'
        ]);
        
        $this->assertEquals(0, $this->getExitCode());
        
        $filePath = APP_PATH . 'Infrastructure' . DS . 'Repository' . DS . 'ProductRepository.php';
        $this->assertTrue(file_exists($filePath));
        
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('class ProductRepository', $content);
        $this->assertStringContainsString('return \'products\'', $content);
    }
}
