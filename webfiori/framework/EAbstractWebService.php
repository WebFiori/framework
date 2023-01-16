<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2020 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework;

use webfiori\http\AbstractWebService;
use webfiori\http\WebServicesManager;
/**
 * A class which represents a web service.
 *
 * @author Ibrahim
 * 
 * @since 2.3.6
 */
abstract class EAbstractWebService extends AbstractWebService {
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
     * @param WebServicesManager|null $manager The manager at which the service 
     * will be associated with. If null is given, the association will be removed if 
     * the service was associated with a manager.
     * 
     */
    public function setManager(WebServicesManager $manager = null) {
        parent::setManager($manager);
    }
}
