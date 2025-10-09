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
namespace WebFiori\Framework\Writers;

use WebFiori\Http\AbstractWebService;
/**
 * A class which is used to hold CLI created services temporary.
 *
 * This class does not hold any web service. The main aim of this class is
 * to hold the service which is created using CLI and later on, create the
 * actual class that contains the web service.
 *
 * @author Ibrahim
 *
 * @version 1.0
 */
class ServiceHolder extends AbstractWebService {
    public function __construct(string $name = '') {
        parent::__construct($name);
    }
    /**
     *
     * @return boolean Always return false.
     */
    public function isAuthorized() {
        return false;
    }
    /**
     * Process the request.
     *
     * Actually, this method does nothing.
     */
    public function processRequest() {
    }
}
