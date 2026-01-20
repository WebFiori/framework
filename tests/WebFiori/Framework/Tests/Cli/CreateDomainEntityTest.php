<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;

/**
 * Test cases for creating domain entities.
 * 
 * @author Ibrahim
 */
class CreateDomainEntityTest extends CLITestCase {
    
    protected function tearDown(): void {
        $this->cleanupDomain();
        parent::tearDown();
    }
    
    private function cleanupDomain(): void {
        $dir = APP_PATH . 'Domain';
        if (is_dir($dir)) {
            foreach (glob($dir . DS . '*.php') as $file) {
                unlink($file);
            }
        }
    }
    
    /**
     * @test
     */
    public function testCreateDomainEntity() {
        $output = $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'domain-entity'
        ], [
            "\n",  // namespace (use default)
            'User',  // class name
            'id',  // property name
            '0',  // type: int
            'y',  // nullable
            'name',  // property name
            '1',  // type: string
            'n',  // not nullable
            'email',  // property name
            '1',  // type: string
            'n',  // not nullable
            "\n"  // finish
        ]);
        
        $this->assertEquals(0, $this->getExitCode());
        
        $filePath = APP_PATH . 'Domain' . DS . 'User.php';
        $this->assertTrue(file_exists($filePath), 'Entity file should exist');
        
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('namespace App\\Domain', $content);
        $this->assertStringContainsString('class User', $content);
        $this->assertStringContainsString('public ?int $id', $content);
        $this->assertStringContainsString('public string $name', $content);
        $this->assertStringContainsString('public string $email', $content);
        
        // Test that class can be loaded
        require_once $filePath;
        $this->assertTrue(class_exists('\\App\\Domain\\User'));
        
        // Test instantiation
        $user = new \App\Domain\User(1, 'John Doe', 'john@example.com');
        $this->assertEquals(1, $user->id);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }
    
    /**
     * @test
     */
    public function testCreateDomainEntityWithDefaults() {
        $output = $this->executeMultiCommand([
            CreateCommand::class,
            '--c' => 'domain-entity',
            '--defaults' => ''
        ], [
            'Product'
        ]);
        
        $this->assertEquals(0, $this->getExitCode());
        
        $filePath = APP_PATH . 'Domain' . DS . 'Product.php';
        $this->assertTrue(file_exists($filePath));
        
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('class Product', $content);
    }
}
