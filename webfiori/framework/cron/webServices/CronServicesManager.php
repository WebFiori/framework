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
namespace webfiori\framework\cron\webServices;

use webfiori\http\WebServicesManager;
/**
 * A class which is used to manage CRON jobs related services.
 *
 * @author Ibrahim
 */
class CronServicesManager extends WebServicesManager {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct();
        $this->addService(new CronLoginService());
        $this->addService(new ForceCronExecutionService());
        $this->addService(new CronLogoutService());
        $this->addService(new GetJobsService());
    }
}
