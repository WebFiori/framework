<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\framework;

use webfiori\framework\i18n\Language;
use webfiori\framework\session\SessionsManager;
use webfiori\http\AbstractWebService;
use webfiori\http\Request;
use webfiori\http\WebServicesManager;
use webfiori\json\Json;
use webfiori\json\JsonI;
/**
 * An extension for the class 'WebServicesManager' that adds support for multi-language 
 * response messages.
 * 
 * The language can be set by sending a GET or POST request that has the 
 * parameter 'lang'.
 * 
 * @author Ibrahim
 * 
 * @version 1.0.2
 */
abstract class ExtendedWebServicesManager extends WebServicesManager {
    /**
     * A constant that represents error message type.
     * 
     * @since 1.0.2
     */
    private static $E = 'error';
    private $translation;
    /**
     * Creates new instance of 'API'.
     * 
     * @param string $version initial API version. Default is '1.0.0'.
     * 
     * @since 1.0
     */
    public function __construct($version = '1.0.0') {
        parent::__construct($version);
        $this->_setTranslation();
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
     * Sends a response message to indicate that an action is not implemented.
     * 
     * This method will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Action not implemented.",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found. 
     * Note that the content of the field "message" might differ. It depends on 
     * the language. If no language is selected or language is not supported, 
     * The language that will be used is the language that was set as default 
     * language in the class 'SiteConfig'.
     * 
     * @since 1.0
     */
    public function actionNotImpl() {
        $message = $this->get('general/action-not-impl');
        $this->sendResponse($message, self::$E, 404);
    }
    /**
     * Sends a response message to indicate that an action is not supported by the API.
     * 
     * This method will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Action not supported",<br/>
     * &nbsp;&nbsp;"type":"error"<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found. 
     * Note that the content of the field "message" might differ. It depends on 
     * the language. If no language is selected or language is not supported, 
     * The language that will be used is the language that was set as default 
     * language in the class 'SiteConfig'.
     * 
     * @since 1.0
     */
    public function actionNotSupported() {
        $message = $this->get('general/action-not-supported');
        $this->sendResponse($message, self::$E, 404);
    }
    /**
     * Sends a response message to indicate that request content type is 
     * not supported by the API.
     * 
     * This method will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Content type not supported.",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * &nbsp;&nbsp;"request-content-type":"content_type"<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found. 
     * Note that the content of the field "message" might differ. It depends on 
     * the language. If no language is selected or language is not supported, 
     * The language that will be used is the language that was set as default 
     * language in the class 'SiteConfig'.
     * 
     * @since 1.1
     */
    public function contentTypeNotSupported($cType = '') {
        $message = $this->get('general/content-not-supported');
        $this->sendResponse($message, self::$E, 404,'"request-content-type":"'.$cType.'"');
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
    public function createLangDir($dir) {
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
            $this->sendResponse($message, self::$E, 404, $json);
        } else {
            $this->sendResponse($message, self::$E, 404, $info);
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
    public function get($dir) {
        return $this->getTranslation()->get($dir);
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
     * @return Language an instance of the class 'Language'.
     * 
     * @since 1.0
     */
    public function getTranslation() {
        return $this->translation;
    }
    /**
     * Sends a response message to indicate that a request parameter(s) have invalid values.
     * 
     * This method will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"The following parameter(s) has invalid values: 'param_1', 'param_2', 'param_n'",<br/>
     * &nbsp;&nbsp;"type":"error"<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found. 
     * Note that the content of the field "message" might differ. It depends on 
     * the language. If no language is selected or language is not supported, 
     * The language that will be used is the language that was set as default 
     * language in the class 'SiteConfig'.
     * 
     * @since 1.3
     */
    public function invParams() {
        $val = '';
        $paramsNamesArr = $this->getInvalidParameters();

        if (gettype($paramsNamesArr) == 'array') {
            $i = 0;
            $count = count($paramsNamesArr);

            foreach ($paramsNamesArr as $paramName) {
                if ($i + 1 == $count) {
                    $val .= '\''.$paramName.'\'';
                } else {
                    $val .= '\''.$paramName.'\', ';
                }
                $i++;
            }
        }
        $message = $this->get('general/inv-params');
        $this->sendResponse($message.$val.'.', self::$E, 404);
    }
    /**
     * Sends a response message to indicate that a request parameter or parameters are missing.
     * 
     * This method will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"The following required parameter(s) where missing from the request body: 'param_1', 'param_2', 'param_n'",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 404 - Not Found. 
     * Note that the content of the field "message" might differ. It depends on 
     * the language. If no language is selected or language is not supported, 
     * The language that will be used is the language that was set as default 
     * language in the class 'SiteConfig'.
     * 
     * @since 1.3
     */
    public function missingParams() {
        $val = '';
        $paramsNamesArr = $this->getMissingParameters();

        if (gettype($paramsNamesArr) == 'array') {
            $i = 0;
            $count = count($paramsNamesArr);

            foreach ($paramsNamesArr as $paramName) {
                if ($i + 1 == $count) {
                    $val .= '\''.$paramName.'\'';
                } else {
                    $val .= '\''.$paramName.'\', ';
                }
                $i++;
            }
        }
        $message = $this->get('general/missing-params');
        $this->sendResponse($message.$val.'.', self::$E, 404);
    }
    /**
     * Sends a response message to indicate that a user is not authorized to 
     * do an API call.
     * 
     * This method will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Not authorized",<br/>
     * &nbsp;&nbsp;"type":"error"<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 401 - Not Authorized. 
     * Note that the content of the field "message" might differ. It depends on 
     * the language. If no language is selected or language is not supported, 
     * The language that will be used is the language that was set as default 
     * language in the class 'SiteConfig'.
     * 
     * @since 1.0
     */
    public function notAuth() {
        $message = $this->get('general/http-codes/401/message');
        $this->sendResponse($message, self::$E, 401);
    }
    /**
     * Auto-register services tables which exist on a specific directory.
     * 
     * Note that the statement 'return __NAMESPACE__' should be included at the 
     * end of service class for auto-register to work. If the statement 
     * does not exist, the method will assume that the path is the namespace of 
     * each class. Also, the classes which represents web services must be suffixed 
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
        WebFioriApp::autoRegister($pathToScan, function (AbstractWebService $ws, ExtendedWebServicesManager $m)
        {
            $m->addService($ws);
        }, 'Service', [$this]);
    }
    /**
     * Sends a response message to indicate that request method is not supported.
     * 
     * This method will send back a JSON string in the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"message":"Method Not Allowed.",<br/>
     * &nbsp;&nbsp;"type":"error",<br/>
     * }
     * </p>
     * In addition to the message, The response will sent HTTP code 405 - Method Not Allowed. 
     * Note that the content of the field "message" might differ. It depends on 
     * the language. If no language is selected or language is not supported, 
     * The language that will be used is the language that was set as default 
     * language in the class 'SiteConfig'.
     * 
     * @since 1.0
     */
    public function requestMethodNotAllowed() {
        $message = $this->get('general/http-codes/405/message');
        $this->sendResponse($message, self::$E, 405);
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
    private function _setTranslation() {
        $reqMeth = Request::getMethod();
        $activeSession = SessionsManager::getActiveSession();

        if ($activeSession !== null) {
            $tempCode = $activeSession->getLangCode(true);
        } else {
            $tempCode = WebFioriApp::getAppConfig()->getPrimaryLanguage();
        }

        if ($reqMeth == 'GET' || $reqMeth == 'DELETE') {
            $langCode = isset($_GET['lang']) ? filter_var($_GET['lang']) : $tempCode;
        } else if ($reqMeth == 'POST' || $reqMeth == 'PUT') {
            $langCode = isset($_POST['lang']) ? filter_var($_POST['lang']) : $tempCode;
        } else {
            $langCode = $tempCode;
        }
        $this->translation = Language::loadTranslation($langCode);
    }
}
