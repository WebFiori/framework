<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\cli\ArrayInputStream;
use webfiori\framework\cli\ArrayOutputStream;
use webfiori\framework\cli\StdIn;
use webfiori\framework\cli\StdOut;
use webfiori\framework\cli\Runner;
use PHPUnit\Framework\TestCase;
/**
 * Description of RunnerTest
 *
 * @author Ibrahim
 */
class RunnerTest extends TestCase {
    /**
     * @test
     */
    public function testSetStreams00() {
        Runner::reset();
        $this->assertTrue(Runner::getOutputStream() instanceof StdOut);
        $this->assertTrue(Runner::getInputStream() instanceof StdIn);
        Runner::setInputStream(new ArrayInputStream());
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertFalse(Runner::getOutputStream() instanceof StdOut);
        $this->assertFalse(Runner::getInputStream() instanceof StdIn);
        $this->assertTrue(Runner::getInputStream() instanceof ArrayInputStream);
        $this->assertTrue(Runner::getOutputStream() instanceof ArrayOutputStream);
    }
    /**
     * @test
     * @depends testSetStreams00
     */
    public function testStart00() {
        $_SERVER['argc'] = 0;
        $this->assertEquals(0, Runner::start());
        $outputArr = Runner::getOutputStream()->getOutputArray();
        $this->assertEquals([
            'Usage:'."\n",
            '    command [arg1 arg2="val" arg3...]'."\n\n",
            'Available Commands:'."\n",
            '    help'."\n",
            '        Display CLI Help. To display help for specific command, use the argument "--command-name" with this command.'."\n\n",
            '    v'."\n",
            '        Display framework version info.'."\n\n",
            '    show-config'."\n",
            '        Display framework configuration.'."\n\n",
            '    list-themes'."\n",
            '        List all registered themes.'."\n\n",
            '    list-jobs'."\n",
            '        List all scheduled CRON jobs.'."\n\n",
            '    list-routes'."\n",
            '        List all created routes and which resource they point to.'."\n\n",
            '    cron'."\n",
            '               Run CRON Scheduler'."\n\n",
            '    route'."\n",
            '        Test the result of routing to a URL'."\n\n",
            '    create'."\n",
            '        Creates a system entity (middleware, web service, background process ...).'."\n\n",
            '    add'."\n",
            '        Add a database connection or SMTP account.'."\n\n",
            '    update-table'."\n",
            '         Update a database table.'."\n\n",
            '    run-query'."\n",
            '        Execute SQL query on specific database.'."\n\n",
            '    update-settings'."\n",
            '        Update application settings which are stored in the class "AppConfig".'."\n\n",
        ], $outputArr);
    }
}
