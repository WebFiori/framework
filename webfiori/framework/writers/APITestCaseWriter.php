<?php

namespace webfiori\framework\writers;

use webfiori\framework\writers\ClassWriter;
use webfiori\http\AbstractWebService;
use webfiori\http\APITestCase;
use webfiori\http\RequestMethod;
use webfiori\http\WebServicesManager;

/**
 * A helper class which is used to write unit test cases for web services.
 *
 * @author Ibrahim
 */
class APITestCaseWriter extends ClassWriter {
    /**
     *
     * @var AbstractWebService
     */
    private $serviceObj;
    private $serviceObjName;
    private $servicesManager;
    private $servicesManagerName;
    /**
     * Creates new instance of the class.
     *
     */
    public function __construct(WebServicesManager $m, $service = null) {
        parent::__construct('WebService', ROOT_PATH.'tests\\apis',  ROOT_PATH.'tests\\apis');
        $this->setSuffix('Test');
        $this->setServicesManager($m);
        
        if (!($service instanceof AbstractWebService)) {
            if (class_exists($service)) {
                $s = new $service();
                
                if ($s instanceof AbstractWebService) {
                    $this->setService($s);
                }
            }
        } else {
            $this->setService($service);
        }
 
    }
    public function getServicesManager() {
        return $this->servicesManager;
    }
    public function setServicesManager(WebServicesManager $m) {
        $this->servicesManager = $m;
        $clazzExp = explode('\\', get_class($m));
        $this->servicesManagerName = $clazzExp[count($clazzExp) - 1];
    }
    /**
     * Returns the web service object which was associated with the writer.
     *
     * @return AbstractWebService
     */
    public function getService() : AbstractWebService {
        return $this->serviceObj;
    }

    /**
     * Sets the table that the writer will use in writing the table class.
     *
     * @param AbstractWebService $service
     */
    public function setService(AbstractWebService $service) {
        $this->serviceObj = $service;
        $clazzExp = explode('\\', get_class($service));
        $this->serviceObjName = $clazzExp[count($clazzExp) - 1];
    }

    /**
     * Write the test case class.
     *
     */
    public function writeClass() {
        $this->addAllUse();
        parent::writeClass();
    }
    public function getServiceName() {
        return $this->serviceObjName;
    }
    public function getServicesManagerName() {
        return $this->servicesManagerName;
    }
    public function writeClassBody() {
        $this->writeNotAllowedRequestMethodTestCases();
        $this->append('}');
    }

    public function writeClassComment() {
        $this->append("/**\n"
                ." * A unit test class which is used to test the API '".$this->getService()->getName()."'.\n"
        );
        $this->append(" * </ul>\n */");
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends APITestCase {');
    }
    private function writeNotAllowedRequestMethodTestCases() {
        $methods = $this->getService()->getRequestMethods();
        $testCasesCount = 0;
        
        foreach (RequestMethod::getAll() as $method) {
            if (!in_array($method, $methods)) {
                $this->append('public function requestMethodNotAllowedTest'.($testCasesCount < 10 ? '0'.$testCasesCount : $testCasesCount).'(){', 1);
                $methodName = $this->getMethName($method);
                
                if ($methodName == 'callEndpoint') {
                    $this->append('$output = $this->'.$methodName.'(new '.$this->getServicesManagerName().'(), RequestMethod::'. strtoupper($method).', '.$this->getServiceName().'::class, []);', 2);
                } else {
                    $this->append('$output = $this->'.$methodName.'(new '.$this->getServicesManagerName().'(), '.$this->getServiceName().'::class, []);', 2);
                }
                $this->append("\$this->assertEquals('{'.self::NL", 2);
                $this->append(". '    \"message\":\"Method Not Allowed.\",'.self::NL", 2);
                $this->append(". '    \"type\":\"error\",'.self::NL", 2);
                $this->append(". '    \"http_code\":405,'.self::NL", 2);
                $this->append(". '}', \$output);", 2);
                $this->append('}', 1);
            }
        }
    }
    private function getMethName($reqMeth) {
        if ($reqMeth == RequestMethod::GET) {
            return 'getRequest';
        } else if ($reqMeth == RequestMethod::PUT) {
            return 'putRequest';
        } else if ($reqMeth == RequestMethod::POST) {
            return 'postRequest';
        } else if ($reqMeth == RequestMethod::DELETE) {
            return 'getRequest';
        } else {
            return 'callEndpoint';
        }
    }
    private function addAllUse() {
        $this->addUseStatement(APITestCase::class);
        $this->addUseStatement(RequestMethod::class);
        $this->addUseStatement(get_class($this->getService()));
        $this->addUseStatement(get_class($this->getServicesManager()));
    }

}
