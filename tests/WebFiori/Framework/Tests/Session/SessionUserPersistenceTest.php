<?php
namespace WebFiori\Framework\Test\Session;

require_once __DIR__.'/SessionDriversTrait.php';

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\InMemorySessionStorage;
use WebFiori\Framework\Session\Session;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\User;

/**
 * Verifies, against every session storage driver, the behavior reported in
 * issue #418: a user attached with Session::setUser() must be persisted to
 * storage and restored when the session is resumed in a later request.
 *
 * Drivers that need an external service (MySQL, MSSQL) are skipped when the
 * service is not reachable.
 */
class SessionUserPersistenceTest extends TestCase {
    /**
     * The user must be readable straight from storage under the reserved
     * '_user' key right after setUser(), without any close() call.
     *
     * @test
     * @dataProvider storageProvider
     */
    public function testSetUserWritesToStorage(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::setStorage($storage);

        $s = new Session(['name' => 'user-write-'.$driver]);
        $s->start();
        $this->assertNull($storage->read($s->getId(), '_user'));

        $s->setUser(new User('john', 'pass', 'john@example.com'));

        $this->assertNotNull(
            $storage->read($s->getId(), '_user'),
            "Issue #418: setUser() did not write '_user' to storage ($driver)."
        );
    }

    /**
     * Issue #418: setUser() must persist the user, and start() must restore
     * it on resume, including user info modified after setUser().
     *
     * @test
     * @dataProvider storageProvider
     */
    public function testUserIsRestoredOnResume(string $driver) {
        SessionsManager::setStorage($this->createStorage($driver));

        $name = 'user-resume-'.$driver;
        $s1 = new Session(['name' => $name]);
        $s1->start();
        $sid = $s1->getId();

        $user = new User('jane.doe', 'secret', 'jane@example.com');
        $user->setID(42);
        $user->setDisplayName('Jane Doe');
        $s1->setUser($user);
        $s1->set('cart', ['apple', 'orange']);

        $this->assertNotNull($s1->getUser());
        $s1->close();

        // A new request resuming the same session.
        $s2 = new Session(['name' => $name, 'session-id' => $sid]);
        $s2->start();

        $this->assertSame(['apple', 'orange'], $s2->get('cart'), 'Session variables should survive resume.');
        $this->assertNotNull($s2->getUser(), "Issue #418: user was not restored on resume ($driver).");
        $this->assertEquals(42, $s2->getUser()->getId());
        $this->assertEquals('jane.doe', $s2->getUser()->getUserName());
        $this->assertEquals('jane@example.com', $s2->getUser()->getEmail());
        $this->assertEquals('Jane Doe', $s2->getUser()->getDisplayName());
    }
    use SessionDriversTrait;

    protected function setUp(): void {
        parent::setUp();
        InMemorySessionStorage::reset();
    }

    protected function tearDown(): void {
        $this->cleanupDrivers();
        SessionsManager::setStorage(new InMemorySessionStorage());
        parent::tearDown();
    }
}
