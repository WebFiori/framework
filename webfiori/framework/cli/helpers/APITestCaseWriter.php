<?php

namespace webfiori\framework\cli\helpers;

use webfiori\framework\writers\ClassWriter;
use webfiori\http\AbstractWebService;
use webfiori\http\APITestCase;

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
    /**
     * Creates new instance of the class.
     *
     */
    public function __construct($tableObj = null) {
        parent::__construct('NewTable', ROOT_PATH.'tests\\apis',  ROOT_PATH.'tests\\apis');
        $this->setSuffix('Test');

 
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
    private function addAllUse() {
        $this->addUseStatement(APITestCase::class);
    }

}
