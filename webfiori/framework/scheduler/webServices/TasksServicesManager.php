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
namespace webfiori\framework\scheduler\webServices;

use webfiori\framework\session\SessionsManager;
use webfiori\http\WebServicesManager;
/**
 * A class which is used to manage scheduled tasks related services.
 *
 * @author Ibrahim
 */
class TasksServicesManager extends WebServicesManager {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct();
        $this->addService(new TasksLoginService());
        $this->addService(new ForceTaskExecutionService());
        $this->addService(new TasksLogoutService());
        $this->addService(new GetTasksService());
        $this->addService(new SetupPasswordService());
    }
}
