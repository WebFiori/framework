<?php
namespace WebFiori\Tests\ServiceRouterFixtures;

/**
 * A helper class that is NOT a WebService or Manager — should be skipped.
 */
class HelperUtil {
    public function doSomething(): string {
        return 'not a service';
    }
}
