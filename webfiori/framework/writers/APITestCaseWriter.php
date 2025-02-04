<?php
namespace webfiori\framework\writers;

use webfiori\http\AbstractWebService;
use webfiori\http\APITestCase;
use webfiori\http\RequestMethod;
use webfiori\http\ResponseMessage;
use webfiori\http\WebServicesManager;

/**
 * A helper class which is used to write unit test cases for web services.
 *
 * @author Ibrahim
 */
class APITestCaseWriter extends ClassWriter {
    private $phpunitV;
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
     * @param WebServicesManager|null $manager Web services manager instance.
     *
     * @param AbstractWebService|string|null $service The web service at which the test case
     * will be based on.
     */
    public function __construct(?WebServicesManager $manager = null, null|string|AbstractWebService $service = null) {
        parent::__construct('WebService', ROOT_PATH.DS.'tests'.DS.'apis', 'tests\\apis');
        $this->setSuffix('Test');

        if ($manager !== null) {
            $this->setServicesManager($manager);
        }
        $this->setPhpUnitVersion(9);

        if (!($service instanceof AbstractWebService)) {
            if ($service !== null && class_exists($service)) {
                $s = new $service();

                if ($s instanceof AbstractWebService) {
                    $this->setService($s);
                }
            }
        } else {
            $this->setService($service);
        }
    }
    /**
     * Returns PHPUnit version number.
     *
     * This is used to check if annotations or attributes should be used in test case
     * method declaration. Starting with PHPUnit 10, attributes are used.
     *
     * @return int
     */
    public function getPhpUnitVersion() : int {
        return $this->phpunitV;
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
     * Returns the name of the class of the web service.
     *
     * @return string
     */
    public function getServiceName() : string {
        return $this->serviceObjName;
    }
    /**
     * Returns the associated web services manager that will be used by the test case.
     *
     * @return WebServicesManager
     */
    public function getServicesManager() : WebServicesManager {
        return $this->servicesManager;
    }
    /**
     * Returns the name of web services manager class.
     *
     * @return string
     */
    public function getServicesManagerName() : string {
        return $this->servicesManagerName;
    }
    /**
     * Sets PHPUnit version number.
     *
     * This is used to check if annotations or attributes should be used in test case
     * method declaration. Starting with PHPUnit 10, attributes are used.
     *
     * @param int $num
     */
    public function setPhpUnitVersion(int $num) {
        $this->phpunitV = $num;
    }

    /**
     * Sets the web service that the writer will use in writing the test case.
     *
     * @param AbstractWebService $service
     */
    public function setService(AbstractWebService $service) {
        $this->serviceObj = $service;
        $clazzExp = explode('\\', get_class($service));
        $this->serviceObjName = $clazzExp[count($clazzExp) - 1];
    }
    /**
     * Sets the associated web services manager that will be used by the test case.
     *
     * @param WebServicesManager $m
     */
    public function setServicesManager(WebServicesManager $m) {
        $this->servicesManager = $m;
        $clazzExp = explode('\\', get_class($m));
        $this->servicesManagerName = $clazzExp[count($clazzExp) - 1];
    }

    /**
     * Write the test case class.
     *
     */
    public function writeClass() {
        $this->addAllUse();
        parent::writeClass();
    }
    public function writeClassBody() {
        $this->writeNotAllowedRequestMethodTestCases();
        $this->writeRequiredParametersTestCases();
        $this->writeTestCases();
        $this->append('}');
    }

    public function writeClassComment() {
        $this->append("/**\n"
                ." * A unit test class which is used to test the API '".$this->getService()->getName()."'.\n"
        );
        $this->append(" */");
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends APITestCase {');
    }
    
    private function addAllUse() {
        $this->addUseStatement(APITestCase::class);
        $this->addUseStatement(RequestMethod::class);
        $this->addUseStatement(get_class($this->getService()));
        $this->addUseStatement(get_class($this->getServicesManager()));
        $this->addUseStatement('PHPUnit\Framework\Attributes\Test');
    }
    private function addTestAnnotation() {
        if ($this->getPhpUnitVersion() >= 10) {
            $this->append('#[Test]', 1);
        } else {
            $this->append('/**', 1);
            $this->append(' * @test', 1);
            $this->append(' */', 1);
        }
    }
    private function getMethName($reqMeth) {
        if ($reqMeth == RequestMethod::GET) {
            return 'getRequest';
        } else {
            if ($reqMeth == RequestMethod::PUT) {
                return 'putRequest';
            } else {
                if ($reqMeth == RequestMethod::POST) {
                    return 'postRequest';
                } else {
                    if ($reqMeth == RequestMethod::DELETE) {
                        return 'getRequest';
                    } else {
                        return 'callEndpoint';
                    }
                }
            }
        }
    }
    private function writeNotAllowedRequestMethodTestCases() {
        $methods = $this->getService()->getRequestMethods();
        $testCasesCount = 0;

        foreach (RequestMethod::getAll() as $method) {
            if (!in_array($method, $methods)) {
                $this->addTestAnnotation();
                $this->append('public function testRequestMethodNotAllowed'.($testCasesCount < 10 ? '0'.$testCasesCount : $testCasesCount).'() {', 1);
                $methodName = $this->getMethName($method);

                if ($methodName == 'callEndpoint') {
                    $this->append('$output = $this->'.$methodName.'(new '.$this->getServicesManagerName().'(), RequestMethod::'.strtoupper($method).', '.$this->getServiceName().'::class, []);', 2);
                } else {
                    $this->append('$output = $this->'.$methodName.'(new '.$this->getServicesManagerName().'(), '.$this->getServiceName().'::class, []);', 2);
                }
                $this->append("\$this->assertEquals('{'.self::NL", 2);
                $this->append(". '    \"message\":\"Method Not Allowed.\",'.self::NL", 2);
                $this->append(". '    \"type\":\"error\",'.self::NL", 2);
                $this->append(". '    \"http_code\":405'.self::NL", 2);
                $this->append(". '}', \$output);", 2);
                $this->append('}', 1);
                $testCasesCount++;
            }
        }
    }
    private function writeRequiredParametersTestCases() {
        $params = $this->getService()->getParameters();
        $responseMessage = ResponseMessage::get('404-2');
        $missingArr = [];

        foreach ($params as $param) {
            if (!$param->isOptional()) {
                $missingArr[] = $param->getName();
            }
        }

        if (count($missingArr) !== 0) {
            $requestMethod = $this->getService()->getRequestMethods()[0];
            $this->addTestAnnotation();
            $this->append('public function testRequiredParameters() {', 1);
            $this->append('$output = $this->callEndpoint(new '.$this->getServicesManagerName().'(), RequestMethod::'.strtoupper($requestMethod).', '.$this->getServiceName().'::class, []);', 2);
            $this->append("\$this->assertEquals('{'.self::NL", 2);
            $this->append(". '    \"message\":\"$responseMessage\'".implode("\',", $missingArr)."\'.\",'.self::NL", 2);
            $this->append(". '    \"type\":\"error\",'.self::NL", 2);
            $this->append(". '    \"http_code\":404,'.self::NL", 2);
            $this->append(". '    \"more_info\":{'.self::NL", 2);
            $this->append(". '        \"missing\":['.self::NL", 2);

            for ($x = 0 ; $x < count($missingArr) ; $x++) {
                $item = $missingArr[$x];

                if ($x + 1 == count($missingArr)) {
                    $this->append(". '            \"$item\"'.self::NL", 2);
                } else {
                    $this->append(". '            \"$item\",'.self::NL", 2);
                }
            }
            $this->append(". '        ]'.self::NL", 2);
            $this->append(". '    }'.self::NL", 2);
            $this->append(". '}', \$output);", 2);
            $this->append('}', 1);
        }
    }
    private function writeTestCases() {
        $methods = $this->getService()->getRequestMethods();
        $testCasesCount = 0;

        foreach (RequestMethod::getAll() as $method) {
            if (in_array($method, $methods)) {
                $this->addTestAnnotation();
                $this->append('public function test'.$method.'Request00() {', 1);
                $this->append("//TODO: Write test case for $method request.", 2);
                $methodName = $this->getMethName($method);

                if (count($this->getService()->getParameters()) == 0) {
                    if ($methodName == 'callEndpoint') {
                        $this->append('$output = $this->'.$methodName.'(new '.$this->getServicesManagerName().'(), RequestMethod::'.strtoupper($method).', '.$this->getServiceName().'::class, []);', 2);
                    } else {
                        $this->append('$output = $this->'.$methodName.'(new '.$this->getServicesManagerName().'(), '.$this->getServiceName().'::class, []);', 2);
                    }
                } else {
                    if ($methodName == 'callEndpoint') {
                        $this->append('$output = $this->'.$methodName.'(new '.$this->getServicesManagerName().'(), RequestMethod::'.strtoupper($method).', '.$this->getServiceName().'::class, [', 2);
                    } else {
                        $this->append('$output = $this->'.$methodName.'(new '.$this->getServicesManagerName().'(), '.$this->getServiceName().'::class, [', 2);
                    }

                    foreach ($this->getService()->getParameters() as $reqParam) {
                        $this->append("'".$reqParam->getName()."' => null,", 3);
                    }
                    $this->append(']);', 2);
                }

                $this->append("\$this->assertEquals('{'.self::NL", 2);
                $this->append(". '}', \$output);", 2);
                $this->append('}', 1);
                $testCasesCount++;
            }
        }
    }
}
