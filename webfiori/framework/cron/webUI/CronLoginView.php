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
namespace webfiori\framework\cron\webUI;

use webfiori\framework\session\SessionsManager;
use webfiori\framework\WebFioriApp;
use webfiori\http\Response;
/**
 * A page which is used to show login form to enter login information to 
 * access cron web interface.
 *
 * @author Ibrahim
 */
class CronLoginView extends CronView {
    public function __construct() {
        parent::__construct('CRON Web Interface Login', 'Login to CRON Control panel.');

        if (SessionsManager::get('cron-login-status')) {
            Response::addHeader('location', WebFioriApp::getAppConfig()->getBaseURL().'/cron/jobs');
            Response::send();
        }
        $row = $this->insert('v-row');
        $row->setAttributes([
            'align' => 'center'
        ]);
        $card = $row->addChild('v-col', [
            'cols' => 12,
            'md' => 4, 'sm' => 12
        ])->addChild('v-card', [
            ':loading' => 'loading',
            ':disabled' => 'loading'
        ]);
        $card->addChild('v-card-text')
            ->addChild('v-text-field', [
                'type' => 'password',
                'v-model' => 'password',
                'label' => 'Enter CRON password here.',
                ':loading' => 'loading',
                '@keydown' => 'checkIfEnterHit'
            ]);
        $card->addChild('v-card-text')
            ->addChild('v-btn', [
                '@click' => 'login',
                'color' => 'primary',
                ':loading' => 'loading',
                ':disabled' => 'login_btn_disabled'
            ])->text('Login');
    }
}
