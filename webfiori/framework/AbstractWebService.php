<?php
namespace webfiori\framework;

use webfiori\http\AbstractWebService;
use webfiori\framework\ExtendedWebServicesManager;
/**
 * A class which represents a web service.
 *
 * @author Ibrahim
 * 
 * @since 2.3.6
 */
abstract class AbstractWebService extends AbstractWebService {
    /**
     * Creates new instance of the class.
     * 
     * The developer can supply an optional service name. 
     * A valid service name must follow the following rules:
     * <ul>
     * <li>It can contain the letters [A-Z] and [a-z].</li>
     * <li>It can contain the numbers [0-9].</li>
     * <li>It can have the character '-' and the character '_'.</li>
     * </ul>
     * If The given name is invalid, the name of the service will be set to 'new-service'.
     * 
     * @param string $name The name of the web service. 
     */
    public function __construct($name) {
        parent::__construct($name);
    }
    /**
     * 
     * @return ExtendedWebServicesManager|null
     */
    public function getManager() {
        return parent::getManager();
    }
    /**
     * Associate the web service with a manager.
     * 
     * The developer does not have to use this method. It is used when a 
     * service is added to a manager.
     * 
     * @param ExtendedWebServicesManager|null $manager The manager at which the service 
     * will be associated with. If null is given, the association will be removed if 
     * the service was associated with a manager.
     * 
     */
    public function setManager($manager) {
        parent::setManager($manager);
    }
}
