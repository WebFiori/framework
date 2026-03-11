<?php
namespace WebFiori\Framework\Test\Config;

use PHPUnit\Framework\TestCase;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Mail\SMTPAccount;
use WebFiori\Framework\Config\ClassDriver;

/**
 * Test environment variable resolution in ClassDriver
 */
class ClassDriverEnvTest extends TestCase {
    
    /**
     * @test
     */
    public function testEnvVarsInDBConnection() {
        putenv('TEST_DB_HOST=192.168.1.100');
        putenv('TEST_DB_USER=testuser');
        putenv('TEST_DB_PASS=testpass123');
        putenv('TEST_DB_NAME=testdb');
        
        $driver = new ClassDriver();
        $driver->initialize(true);
        
        // Add connection with env: values
        $conn = new ConnectionInfo('mysql', 'env:TEST_DB_USER', 'env:TEST_DB_PASS', 'env:TEST_DB_NAME');
        $conn->setHost('env:TEST_DB_HOST');
        $conn->setName('test_conn');
        $driver->addOrUpdateDBConnection($conn);
        
        // Retrieve and verify resolution
        $retrieved = $driver->getDBConnection('test_conn');
        $this->assertEquals('192.168.1.100', $retrieved->getHost());
        $this->assertEquals('testuser', $retrieved->getUsername());
        $this->assertEquals('testpass123', $retrieved->getPassword());
        $this->assertEquals('testdb', $retrieved->getDBName());
        
        $driver->remove();
    }
    
    /**
     * @test
     */
    public function testEnvVarsInSMTPConnection() {
        putenv('TEST_SMTP_HOST=smtp.test.com');
        putenv('TEST_SMTP_USER=test@example.com');
        putenv('TEST_SMTP_PASS=smtppass');
        putenv('TEST_SMTP_FROM=noreply@test.com');
        
        $driver = new ClassDriver();
        $driver->initialize(true);
        
        // Add SMTP with env: values
        $smtp = new SMTPAccount();
        $smtp->setAccountName('test_smtp');
        $smtp->setServerAddress('env:TEST_SMTP_HOST');
        $smtp->setUsername('env:TEST_SMTP_USER');
        $smtp->setPassword('env:TEST_SMTP_PASS');
        $smtp->setAddress('env:TEST_SMTP_FROM');
        $smtp->setSenderName('Test Sender');
        $smtp->setPort(587);
        $driver->addOrUpdateSMTPAccount($smtp);
        
        // Retrieve and verify resolution
        $retrieved = $driver->getSMTPConnection('test_smtp');
        $this->assertEquals('smtp.test.com', $retrieved->getServerAddress());
        $this->assertEquals('test@example.com', $retrieved->getUsername());
        $this->assertEquals('smtppass', $retrieved->getPassword());
        $this->assertEquals('noreply@test.com', $retrieved->getAddress());
        
        $driver->remove();
    }
    
    /**
     * @test
     */
    public function testEnvVarsInEnvVars() {
        putenv('TEST_API_KEY=secret_key_123');
        
        $driver = new ClassDriver();
        $driver->initialize(true);
        
        $driver->addEnvVar('MY_API_KEY', 'env:TEST_API_KEY', 'API Key from environment');
        
        $vars = $driver->getEnvVars();
        $this->assertEquals('secret_key_123', $vars['MY_API_KEY']['value']);
        
        $driver->remove();
    }
    
    /**
     * @test
     */
    public function testEnvVarsInSchedulerPassword() {
        putenv('TEST_SCHEDULER_PASS=hashed_password_123');
        
        $driver = new ClassDriver();
        $driver->initialize(true);
        
        $driver->setSchedulerPassword('env:TEST_SCHEDULER_PASS');
        
        $this->assertEquals('hashed_password_123', $driver->getSchedulerPassword());
        
        $driver->remove();
    }
    
    /**
     * @test
     */
    public function testFallbackWhenEnvNotSet() {
        $driver = new ClassDriver();
        $driver->initialize(true);
        
        // Add connection with env: that doesn't exist
        $conn = new ConnectionInfo('mysql', 'env:NONEXISTENT_USER', 'pass', 'db');
        $conn->setHost('env:NONEXISTENT_HOST');
        $conn->setName('fallback_test');
        $driver->addOrUpdateDBConnection($conn);
        
        // Should fallback to original value
        $retrieved = $driver->getDBConnection('fallback_test');
        $this->assertEquals('env:NONEXISTENT_HOST', $retrieved->getHost());
        $this->assertEquals('env:NONEXISTENT_USER', $retrieved->getUsername());
        
        $driver->remove();
    }
}
