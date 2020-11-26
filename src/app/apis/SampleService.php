<?php

namespace webfiori\examples\webApis;

use webfiori\framework\ExtendedWebServicesManager;
use webfiori\http\AbstractWebService;
use webfiori\framework\WebFiori;
/**
 * A sample service that can be used as a reference when creating web services.
 *
 * @author Ibrahim
 */
class SampleService extends AbstractWebService {
    public function __construct() {
        parent::__construct('say-hello');
        $this->addRequestMethod('get');
    }
    public function isAuthorized() {
        
    }

    public function processRequest() {
        $manager = $this->getManager();
        if ($manager instanceof ExtendedWebServicesManager) {
            $lang = $manager->getTranslation()->getCode();
        } else {
            WebFiori::getSiteConfig()->getPrimaryLanguage();
        }

        if ($lang == 'AR') {
            $this->send('text/html', '<html><head><title>قُل مرحباً</title></head><body><p dir="rtl">مرحباً بالعالم!</p></body></html>');
        } else {
            $this->send('text/html', '<html><head><title>Say Hello</title></head><body><p>hello world!</p></body></html>');
        }
    }

}
