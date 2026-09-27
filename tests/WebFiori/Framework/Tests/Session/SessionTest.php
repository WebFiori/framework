<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\File\File;
use WebFiori\Framework\App;
use WebFiori\Framework\Exceptions\SessionException;
use WebFiori\Framework\Session\InMemorySessionStorage;
use WebFiori\Framework\Session\Session;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Session\SessionStatus;
use WebFiori\Framework\User;
/**
 * Description of SessionTest
 *
 * @author Eng.Ibrahim
 */
class SessionTest extends TestCase {
    /**
     * @test
     */
    public function testClose00() {
        $_POST['lang'] = 'EN';
        App::getRequest()->setRequestMethod('POST');
        $session = new Session(['name' => 'new']);
        $session->start();
        $session->set('hello','world');
        // With per-key storage, the write is already persisted (no blob file).
        // Verify the key is readable from storage before close.
        $this->assertEquals('world', $session->get('hello'));
        $session->close();
        $this->assertFalse($session->isRunning());
        $this->assertEquals(0,$session->getStartedAt());
        $this->assertEquals(0,$session->getResumedAt());
        $this->assertNull($session->get('hello'));
    }
    /**
     * @test
     */
    public function testConstructor00() {
        $sesston = new Session([
            'name' => 'my-new-sesstion'
        ]);
        $this->assertEquals('my-new-sesstion',$sesston->getName());
        $this->assertEquals(7200,$sesston->getDuration());
        $this->assertEquals(0,$sesston->getStartedAt());
        $this->assertEquals(0,$sesston->getResumedAt());
        $this->assertEquals(0,$sesston->getPassedTime());
        $this->assertEquals('', $sesston->getLangCode());
        //$this->assertNull($sesston->getUser());
        $this->assertNotNull($sesston->getId());
        $this->assertEquals(SessionStatus::INACTIVE,$sesston->getStatus());
    }
    /**
     * @test
     */
    public function testConstructor01() {
        $sesston = new Session([
            'name' => 'my-new-sessionx',
            'duration' => 2,
            'session-id' => 'super'
        ]);
        $this->assertEquals('my-new-sessionx',$sesston->getName());
        $this->assertEquals(120,$sesston->getDuration());
        $this->assertEquals(0,$sesston->getStartedAt());
        $this->assertEquals(0,$sesston->getResumedAt());
        $this->assertEquals(0,$sesston->getPassedTime());
        $this->assertEquals('', $sesston->getLangCode());
        $this->assertEquals('', $sesston->getLangCode(true));
        $this->assertNull($sesston->getUser());
        $this->assertEquals('super',$sesston->getId());
        $this->assertEquals(SessionStatus::INACTIVE,$sesston->getStatus());
    }
    /**
     * @test
     */
    public function testConstructor02() {
        $session = new Session([
            'name' => 'wf-session'
        ]);
        $this->assertEquals('wf-session',$session->getName());
        $this->assertEquals(7200,$session->getDuration());
        $this->assertEquals(120 * 60,$session->getRemainingTime());
        $this->assertEquals(0,$session->getStartedAt());
        $this->assertEquals(0,$session->getResumedAt());
        $this->assertEquals(0,$session->getPassedTime());
        $this->assertEquals('', $session->getLangCode());
        //$this->assertNull($session->getUser());
        $this->assertEquals(SessionStatus::INACTIVE,$session->getStatus());
        $cookie = $session->getCookie();
        $this->assertEquals(time() + 7200, $cookie->getExpires());
        $this->assertEquals(date(DATE_COOKIE, Session::DEFAULT_SESSION_DURATION * 60 + time()), $cookie->getLifetime());
        $this->assertEquals('/', $cookie->getPath());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertEquals('Lax', $cookie->getSameSite());
        $this->assertTrue($session->isPersistent());
        $this->assertFalse($session->isRunning());
        $this->assertFalse($session->isRefresh());
    }
    /**
     * @test
     */
    public function testConstructor03() {
        $session = new Session([
            'refresh' => true,
            'duration' => 0,
            'name' => 'hello'
        ]);
        $this->assertFalse($session->isPersistent());
        $this->assertFalse($session->isRunning());
        $this->assertFalse($session->isRefresh());
        $cookie = $session->getCookie();
        $this->assertEquals('', $cookie->getLifetime());
        $this->assertEquals('/', $cookie->getPath());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertEquals('Lax', $cookie->getSameSite());
    }
    /**
     * @test
     */
    public function testConstructor04() {
        $this->expectException(SessionException::class);
        $this->expectExceptionMessage("Invalid session name: ''.");
        $session = new Session();
    }
    /**
     * @test
     */
    public function testConstructor05() {
        $_SERVER['REMOTE_ADDR'] = '::1';
        $session = new Session([
            'refresh' => true,
            'duration' => 0,
            'name' => 'hello'
        ]);
        $this->assertEquals('127.0.0.1', $session->getIp());
    }
    /**
     * @test
     */
    public function testCookieHeader() {
        $s = new Session([
            'name' => 'super-session'
        ]);
        $cookie = $s->getCookie();
        $cookie->setDomain();
        $this->assertEquals('super-session='.$s->getId().'; '
                .'expires='.$cookie->getLifetime().'; '
                .'path=/; Secure; HttpOnly; SameSite=Lax',$s->getCookieHeader());
        $s->setSameSite('None');
        $this->assertEquals('super-session='.$s->getId().'; '
                .'expires='.$cookie->getLifetime().'; '
                .'path=/; Secure; HttpOnly; SameSite=None',$s->getCookieHeader());
        $s->setSameSite(' Strict');
        $this->assertEquals('super-session='.$s->getId().'; '
                .'expires='.$cookie->getLifetime().'; '
                .'path=/; Secure; HttpOnly; SameSite=Strict',$s->getCookieHeader());
        $s->setDuration(0);
        $this->assertEquals('super-session='.$s->getId().'; '
                .'path=/; Secure; HttpOnly; SameSite=Strict',$s->getCookieHeader());
    }
    /**
     * @test
     */
    public function testRemainingTime() {
        $s = new Session(['name' => 'session','duration' => 0.1]);
        $s->start();
        $this->assertEquals(6, $s->getDuration());
        $sessionId = $s->getId();
        $s->close();
        sleep(7);

        // Create new session with same ID to simulate cookie persistence
        $s2 = new Session(['name' => 'session','duration' => 0.1, 'session-id' => $sessionId]);
        $s2->start();

        $this->assertEquals(-1, $s2->getRemainingTime());
    }
    /**
     * @test
     */
    public function testSetVar00() {
        $session = new Session(['name' => 'new']);
        $this->assertFalse($session->has('test'));
        $session->start();
        $this->assertFalse($session->has('test'));
        $session->set('super', 700);
        $this->assertTrue($session->has('super'));
        $this->assertEquals(700, $session->get('super'));
        $var = $session->pull('super');
        $this->assertEquals(700, $var);
        $this->assertFalse($session->has('super'));
    }
    /**
     * @test
     */
    public function testStart00() {
        $_POST['lang'] = 'EN';
        App::getRequest()->setRequestMethod('POST');
        $session = new Session(['name' => 'new']);
        $this->assertEquals(SessionStatus::INACTIVE,$session->getStatus());
        $this->assertEquals(0,$session->getStartedAt());
        $this->assertFalse($session->isRunning());
        $this->assertEquals(0,$session->getResumedAt());
        $session->set('hello','world');
        $this->assertNull($session->get('hello'));
        $this->assertEquals('', $session->getLangCode());
        $session->start();
        $this->assertEquals('EN', $session->getLangCode());
        $this->assertEquals('EN', $session->getLangCode(true));
        $_POST['lang'] = 'AR';
        App::getRequest()->setRequestMethod('POST');
        $this->assertEquals('EN', $session->getLangCode());
        $this->assertEquals('AR', $session->getLangCode(true));
        $this->assertEquals(0,$session->getPassedTime());
        $this->assertEquals(SessionStatus::NEW,$session->getStatus());
        $this->assertEquals(time(),$session->getStartedAt());
        $this->assertEquals(time(),$session->getResumedAt());
        $this->assertTrue($session->isRunning());
        $session->set('hello','world');
        $this->assertEquals('world',$session->get('hello'));
    }
    /**
     * @test
     */
    public function testStart01() {
        $_POST['lang'] = 'EN';
        App::getRequest()->setRequestMethod('POST');
        $session = new Session(['name' => 'new']);
        $session->start();
        $session->set('hello','world');
        $session->close();
        $this->assertEquals(0,$session->getPassedTime());
        sleep(1);
        $session->start();
        $this->assertTrue($session->getStatus() == SessionStatus::RESUMED || $session->getStatus() == SessionStatus::NEW);
        $startedAt = $session->getStartedAt();
        $this->assertTrue($startedAt > 0, "Session should have a valid start time, got: $startedAt");
        $this->assertEquals(time(),$session->getResumedAt());
        $passedTime = $session->getPassedTime();
        $this->assertTrue($passedTime >= 0, "Passed time should be non-negative, got: $passedTime");
        $this->assertEquals('world',$session->get('hello'));
    }
    /**
     * @test
     */
    public function testToJsonTest00() {
        $_POST['lang'] = 'fr';
        App::getRequest()->setRequestMethod('POST');
        $s = new Session(['name' => 'session','duration' => 1]);
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session",'
                .'"started_at":0,'
                .'"duration":60,'
                .'"resumed_at":0,'
                .'"passed_time":0,'
                .'"remaining_time":60,'
                .'"language":"",'
                .'"id":"'.$s->getId().'",'
                .'"is_refresh":false,'
                .'"is_persistent":true,'
                .'"status":"none",'
                .'"user":null,'
                .'"vars":{}}',$j.'');
        $s->start();
        // $j = $s->toJSON();
        // $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session",'
                .'"startedAt":'.$s->getStartedAt().','
                .'"duration":60,'
                .'"resumedAt":'.$s->getStartedAt().','
                .'"passedTime":0,'
                .'"remainingTime":60,'
                .'"language":"FR",'
                .'"id":"'.$s->getId().'",'
                .'"isRefresh":false,'
                .'"isPersistent":true,'
                .'"status":"new",'
                .'"user":null,'
                .'"vars":{}}',$s.'');
    }
    /**
     * @test
     */
    public function testToJsonTest01() {
        $_POST['lang'] = 'fr';
        App::getRequest()->setRequestMethod('POST');
        $s = new Session(['name' => 'session','duration' => 1]);
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session",'
                .'"started_at":0,'
                .'"duration":60,'
                .'"resumed_at":0,'
                .'"passed_time":0,'
                .'"remaining_time":60,'
                .'"language":"",'
                .'"id":"'.$s->getId().'",'
                .'"is_refresh":false,'
                .'"is_persistent":true,'
                .'"status":"none",'
                .'"user":null,'
                .'"vars":{}}',$j.'');
        $s->start();
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session",'
                .'"started_at":'.$s->getStartedAt().','
                .'"duration":60,'
                .'"resumed_at":'.$s->getStartedAt().','
                .'"passed_time":0,'
                .'"remaining_time":60,'
                .'"language":"FR",'
                .'"id":"'.$s->getId().'",'
                .'"is_refresh":false,'
                .'"is_persistent":true,'
                .'"status":"new",'
                .'"user":null,'
                .'"vars":{}}',$j.'');
        $_POST['lang'] = 'enx';
        $this->assertEquals('FR', $s->getLangCode(true));
        $_POST['lang'] = 'En';
        $this->assertEquals('EN', $s->getLangCode(true));
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session",'
                .'"started_at":'.$s->getStartedAt().','
                .'"duration":60,'
                .'"resumed_at":'.$s->getStartedAt().','
                .'"passed_time":0,'
                .'"remaining_time":60,'
                .'"language":"EN",'
                .'"id":"'.$s->getId().'",'
                .'"is_refresh":false,'
                .'"is_persistent":true,'
                .'"status":"new",'
                .'"user":null,'
                .'"vars":{}}',$j.'');
    }

    /**
     * @test
     *
     * The session user set via Session::setUser() must be persisted to storage
     * and survive a close() + resume round-trip, including any user info that
     * is modified before the session is closed.
     *
     * NOTE: This asserts the DESIRED behavior. In the current per-key session
     * system the user is only kept in memory (Session::$sessionUser) and is not
     * written by persistMeta(), so this test will FAIL until user persistence
     * is implemented (persist the user on write/close and restore it in
     * start()).
     */
    public function testUserIsPersistedAcrossResume() {
        // Storage is configured by setUp() to use InMemorySessionStorage.

        // First instance: set a user with some info, then modify that info.
        $s1 = new Session(['name' => 'user-persist-test']);
        $s1->start();
        $sid = $s1->getId();

        $user = new User('jane.doe', 'secret', 'jane@example.com');
        $user->setID(42);
        $user->setDisplayName('Jane Doe');
        $s1->setUser($user);
        $s1->set('cart', ['apple', 'orange']);

        // Change the user's info after it was attached to the session.
        $s1->getUser()->setEmail('jane.doe@work.example.com');
        $s1->getUser()->setDisplayName('Jane D.');

        // Sanity: within the same instance the user and its info are available.
        $this->assertNotNull($s1->getUser());
        $this->assertEquals(42, $s1->getUser()->getId());
        $this->assertEquals('jane.doe', $s1->getUser()->getUserName());
        $this->assertEquals('jane.doe@work.example.com', $s1->getUser()->getEmail());
        $this->assertEquals('Jane D.', $s1->getUser()->getDisplayName());

        $s1->close();

        // Second instance with the same session ID (a resumed request).
        $s2 = new Session(['name' => 'user-persist-test', 'session-id' => $sid]);
        $s2->start();

        // A normal session variable survives the round-trip.
        $this->assertSame(['apple', 'orange'], $s2->get('cart'));

        // The user must survive the round-trip too...
        $this->assertNotNull(
            $s2->getUser(),
            'Session user should be persisted and restored across resume.'
        );

        // ...along with all of its (including modified) info.
        $this->assertEquals(42, $s2->getUser()->getId());
        $this->assertEquals('jane.doe', $s2->getUser()->getUserName());
        $this->assertEquals('jane.doe@work.example.com', $s2->getUser()->getEmail());
        $this->assertEquals('Jane D.', $s2->getUser()->getDisplayName());
    }

    /**
     * @test
     *
     * Regression test for issue #422.
     *
     * reGenerateID() only changes the session cookie value; it does not
     * migrate the existing per-key rows (application keys, '_meta', '_user')
     * to the new session ID. The session-fixation login flow regenerates the
     * ID and then attaches the user WITHOUT closing the session before the
     * request ends (the start-session middleware's afterSend() only calls
     * validateStorage()):
     *
     *   $session->set('csrf', 'abc'); // app data under the OLD id
     *   $session->reGenerateID();      // new id (cookie only)
     *   $session->setUser($user);      // writes _user under the NEW id
     *
     * As a result, the new ID ends up with only '_user' (written eagerly by
     * setUser()) while '_meta', the user's companion state and all previously
     * stored application keys remain orphaned under the OLD id. On the next
     * request start() reads '_meta' for the new id, finds null, treats it as a
     * brand-new session and loses everything.
     *
     * The whole session state must be carried across a reGenerateID(). This
     * test asserts the DESIRED behavior, so it FAILS against current code until
     * #422 is fixed.
     */
    public function testSessionStateSurvivesRegenerateIdWithoutClose() {
        // Request 1: anonymous session with some application data, then login.
        $s1 = new Session(['name' => 'regen-test']);
        $s1->start();                          // writes _meta under the old id
        $s1->set('csrf', 'abc123');            // custom app key (old id)
        $s1->set('cart', ['apple', 'orange']); // another custom key (old id)
        $s1->set('lang_pref', 'ar');           // another custom key (old id)

        $s1->reGenerateID();                   // new id (cookie value only)
        $newId = $s1->getId();

        $user = new User('jane.doe', 'secret', 'jane@example.com');
        $user->setID(7);
        $s1->setUser($user);                   // writes _user under the NEW id
        // NOTE: intentionally NO close() before the "request" ends.

        // Request 2: resume the NEW id (as the next request would).
        $s2 = new Session(['name' => 'regen-test', 'session-id' => $newId]);
        $s2->start();

        // The user must survive.
        $this->assertNotNull(
            $s2->getUser(),
            'Issue #422: user set after reGenerateID() must survive resume.'
        );
        $this->assertEquals(7, $s2->getUser()->getId());
        $this->assertEquals('jane.doe', $s2->getUser()->getUserName());

        // ALL custom application keys must survive the ID regeneration too.
        $this->assertSame('abc123', $s2->get('csrf'),
            'Issue #422: custom key "csrf" must survive reGenerateID().');
        $this->assertSame(['apple', 'orange'], $s2->get('cart'),
            'Issue #422: custom key "cart" must survive reGenerateID().');
        $this->assertSame('ar', $s2->get('lang_pref'),
            'Issue #422: custom key "lang_pref" must survive reGenerateID().');

        // And they must be readable via getVars() (which excludes reserved keys).
        $vars = $s2->getVars();
        $this->assertArrayHasKey('csrf', $vars);
        $this->assertArrayHasKey('cart', $vars);
        $this->assertArrayHasKey('lang_pref', $vars);
        $this->assertArrayNotHasKey('_user', $vars);
        $this->assertArrayNotHasKey('_meta', $vars);
    }

    /**
     * @test
     *
     * Mutating a reserved key (set/remove/pull) must throw a SessionException.
     */
    public function testReservedKeysRejectMutation() {
        $session = new Session(['name' => 'reserved-test']);
        $session->start();

        foreach (Session::RESERVED_KEYS as $reserved) {
            $threwOnSet = false;

            try {
                $session->set($reserved, 'x');
            } catch (SessionException $ex) {
                $threwOnSet = true;
                $this->assertStringContainsString($reserved, $ex->getMessage());
            }
            $this->assertTrue($threwOnSet, "set('$reserved') should throw a SessionException.");

            $threwOnRemove = false;

            try {
                $session->remove($reserved);
            } catch (SessionException $ex) {
                $threwOnRemove = true;
            }
            $this->assertTrue($threwOnRemove, "remove('$reserved') should throw a SessionException.");

            $threwOnPull = false;

            try {
                $session->pull($reserved);
            } catch (SessionException $ex) {
                $threwOnPull = true;
            }
            $this->assertTrue($threwOnPull, "pull('$reserved') should throw a SessionException.");
        }
    }

    /**
     * @test
     *
     * Reading a reserved key via get()/has() is allowed (read-only is safe).
     */
    public function testReservedKeysAllowRead() {
        $session = new Session(['name' => 'reserved-read-test']);
        $session->start();

        foreach (Session::RESERVED_KEYS as $reserved) {
            // Should not throw; get() returns whatever is stored (or null),
            // has() returns a bool. We only assert no exception is raised.
            $session->get($reserved);
            $this->assertIsBool($session->has($reserved));
        }
    }

    protected function setUp(): void {
        InMemorySessionStorage::reset();
        SessionsManager::setStorage(new InMemorySessionStorage());
    }
}
