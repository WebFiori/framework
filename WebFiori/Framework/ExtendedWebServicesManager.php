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
namespace WebFiori\Framework;

use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\AbstractWebService;
use WebFiori\Http\Request;
use WebFiori\Http\WebServicesManager;
use WebFiori\Json\Json;
use WebFiori\Json\JsonI;
/**
 * An extension for the class 'WebServicesManager' that adds support for multi-language
 * response messages.
 *
 * The language can be set by sending a GET or POST request that has the
 * parameter 'lang'.
 *
 * @author Ibrahim
 */
abstract class ExtendedWebServicesManager extends WebServicesManager {
    private $translation;
    /**
     * Creates new instance of 'API'.
     *
     * @param string $version initial API version. Default is '1.0.0'.
     *
     * @since 1.0
     */
    public function __construct(string $version = '1.0.0') {
        parent::__construct($version);
        $this->setTranslationHelper();
        $langCode = $this->getTranslation()->getCode();
        $generalDir = 'general';
        $this->createLangDir($generalDir);

        if ($langCode == 'AR') {
            $this->setLangVars($generalDir, [
                'action-not-supported' => 'العملية غير مدعومة.',
                'content-not-supported' => 'نوع المحتوى غير مدعوم.',
                'action-not-impl' => 'لم يتم تنفيذ العملية.',
                'missing-params' => 'المعاملات التالية مفقودة من جسم الطلب: ',
                'inv-params' => 'المُعاملات التالية لديها قيم غير صالحة: ',
                'db-error' => 'خطأ في قاعدة البيانات.'
            ]);
        } else {
            $this->setLangVars($generalDir, [
                'action-not-supported' => 'Action is not supported by the API.',
                'content-not-supported' => 'Content type not supported.',
                'action-not-impl' => 'API action is not implemented yet.',
                'missing-params' => 'The following required parameter(s) where missing from the request body: ',
                'inv-params' => 'The following parameter(s) has invalid values: ',
                'db-error' => 'Database Error.'
            ]);
        }
    }
    /**
     * Creates a sub array to define language variables.
     *
     * An example: if the given string is 'general',
     * an array with key name 'general' will be created. Another example is
     * if the given string is 'pages/login', two arrays will be created. The
     * top one will have the key value 'pages' and another one inside
     * the pages array with key value 'login'.
     *
     * @param string $dir A string that looks like a directory.
     *
     * @since 1.0
     */
    public function createLangDir(string $dir) {
        $this->getTranslation()->createDirectory($dir);
    }
    /**
     * Sends a response message to indicate that a database error has occur.
     *
     * This method will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"a_message",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * &nbsp;&nbsp;"err-info":OTHER_DATA<br/>
     * }
     * </p>
     * In here, 'OTHER_DATA' can be a basic string.
     * Also, The response will sent HTTP code 404 - Not Found.
     *
     * @param JsonI|Json|DB|string $info An object of type JsonI or
     * Json that describe the error in more details. Also it can be a simple string.
     * Also, this parameter can be a database instance that contains database error
     * information.
     * Note that the content of the field "message" might differ. It depends on
     * the language. If no language is selected or language is not supported,
     * The language that will be used is the language that was set as default
     * language in the class 'SiteConfig'.
     *
     * @since 1.0
     */
    public function databaseErr($info = '') {
        $message = $this->get('general/db-error');

        if ($info instanceof DB) {
            $dbErr = $info->getLastError();
            $json = new Json([
                'error-message' => $dbErr['message'],
                'error-code' => $dbErr['code']
            ]);

            if (defined('WF_VERBOSE') && WF_VERBOSE === true) {
                $json->add('query', $info->getLastQuery());
            }
            $this->sendResponse($message, self::E, 404, $json);
        } else {
            $this->sendResponse($message, self::E, 404, $info);
        }
    }
    /**
     * Returns the value of a language variable.
     *
     * @param string $dir A directory to the language variable (such as 'pages/login/login-label').
     *
     * @return string|array If the given directory represents a label, the
     * method will return its value. If it represents an array, the array will
     * be returned. If nothing was found, the returned value will be the passed
     * value to the method.
     *
     * @since 1.0
     */
    public function get(string $dir) {
        return Lang::getLabel($dir);
    }
    /**
     * Returns an associative array that contains HTTP authorization header
     * content.
     *
     * The generated associative array will have two indices:
     * <ul>
     * <li><b>type</b>: Type of authorization (e.g. basic, bearer )</li>
     * <li><b>credentials</b>: Depending on authorization type,
     * this field will have different values.</li>
     * </ul>
     * Note that if no authorization header is sent, The two indices will be empty.
     *
     * @return array An associative array.
     *
     * @since 1.0.1
     */
    public function getAuthorizationHeader() {
        $retVal = [
            'type' => '',
            'credentials' => ''
        ];
        $headers = Util::getRequestHeaders();

        if (isset($headers['authorization'])) {
            $split = explode(' ', $headers['authorization']);

            if (count($split) == 2) {
                $retVal['type'] = strtolower($split[0]);
                $retVal['credentials'] = $split[1];
            }
        }

        return $retVal;
    }
    /**
     * Returns the language instance which is linked with the API instance.
     *
     * @return Lang an instance of the class 'Lang'.
     *
     * @since 1.0
     */
    public function getTranslation() {
        return $this->translation;
    }
    /**
     * Auto-register services Tables which exist on a specific directory.
     *
     * The classes which represents web services must be suffixed
     * with the word 'Service' in order to register them (e.g. RegisterUserService).
     *
     * @param string $pathToScan A path which is relative to application source
     * code folder. For example, if application folder name is 'my-app' and
     * the web services are in the folder 'my-app/apis/user,
     * then the value of the argument must be 'apis/user'.
     *
     * @since 1.0.1
     */
    public function registerServices($pathToScan) {
        App::autoRegister($pathToScan, function (AbstractWebService $ws, ExtendedWebServicesManager $m)
        {
            $m->addService($ws);
        }, 'Service', [$this], [$this]);
    }
    /**
     * Sets multiple language variables.
     *
     * @param string $dir A string that looks like a
     * directory.
     *
     * @param array $arr An associative array. The key will act as the variable
     * and the value of the key will act as the variable value.
     *
     * @since 1.0
     */
    public function setLangVars($dir,$arr = []) {
        $this->getTranslation()->setMultiple($dir, $arr);
    }
    /**
     * Set the language at which the API is going to use for the response.
     */
    private function setTranslationHelper() {
        $reqMeth = Request::getMethod();
        $activeSession = SessionsManager::getActiveSession();

        if ($activeSession !== null) {
            $tempCode = $activeSession->getLangCode(true);
        } else {
            $tempCode = App::getConfig()->getPrimaryLanguage();
        }

        if ($reqMeth == 'GET' || $reqMeth == 'DELETE') {
            $langCode = isset($_GET['lang']) ? filter_var($_GET['lang']) : $tempCode;
        } else if ($reqMeth == 'POST' || $reqMeth == 'PUT') {
            $langCode = isset($_POST['lang']) ? filter_var($_POST['lang']) : $tempCode;
        } else {
            $langCode = $tempCode;
        }
        $this->translation = Lang::loadTranslation($langCode);
    }
}
