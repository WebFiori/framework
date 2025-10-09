<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Http\AbstractWebService;
use WebFiori\Http\ParamOption;
use WebFiori\Http\ParamType;
use WebFiori\Http\RequestMethod;
use WebFiori\Http\RequestParameter;

/**
 *
 * @author Ibrahim
 */
class CreateWebServiceTest extends CLITestCase {
    /**
     * @test
     */
    public function test00() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create'
        ], [
            '2',
            'NewWeb',
            "\n", // Hit Enter to pick default value (App\Apis)
            'get-hello',
            'Service Desc',
            "\n", // Hit Enter to pick default value (GET method)
            'n',
            'y',
            'name',
            '6',
            'Random desc',
            'n',
            'n',
            'n',
            "\n", // Hit Enter to pick default value
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background Task.\n",
            "4: Middleware.\n",
            "5: CLI Command.\n",
            "6: Theme.\n",
            "7: Database access class based on table.\n",
            "8: Complete REST backend (Database table, entity, database access and web services).\n",
            "9: Web service test case.\n",
            "10: Database migration.\n",
            "11: Quit. <--\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\Apis'\n",
            "Enter a name for the new web service:\n",
            "Description:\n",
            "Request method:\n",
            "0: CONNECT\n",
            "1: DELETE\n",
            "2: GET <--\n",
            "3: HEAD\n",
            "4: OPTIONS\n",
            "5: PATCH\n",
            "6: POST\n",
            "7: PUT\n",
            "8: TRACE\n",
            "Would you like to add another request method?(y/N)\n",
            "Would you like to add request parameters to the service?(y/N)\n",
            "Enter a name for the request parameter:\n",
            "Choose parameter type:\n",
            "0: array <--\n",
            "1: boolean\n",
            "2: email\n",
            "3: double\n",
            "4: integer\n",
            "5: json-obj\n",
            "6: string\n",
            "7: url\n",
            "Description:\n",
            "Is this parameter optional?(Y/n)\n",
            "Are empty values allowed?(y/N)\n",
            "Would you like to set minimum and maximum length?(y/N)\n",
            "Success: New parameter added.\n",
            "Would you like to add another parameter?(y/N)\n",
            "Creating the class...\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'App'.DS."Apis\".\n",
            "Info: Don't forget to add the service to a services manager.\n",
        ], $output);
        
        $clazz = '\\App\\Apis\\NewWebService';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass('\\App\\Apis\\NewWebService');
        $instance = new $clazz();
        $instance instanceof AbstractWebService;
        $this->assertEquals('get-hello', $instance->getName());
        $this->assertEquals(1, count($instance->getParameters()));
        $this->assertEquals('Service Desc', $instance->getDescription());
        $this->assertEquals([RequestMethod::GET], $instance->getRequestMethods());
        $param00 = $instance->getParameters()[0];
        $this->assertRequestParameter($param00, [
                ParamOption::NAME => 'name',
                ParamOption::TYPE => ParamType::STRING,
                ParamOption::DESCRIPTION => 'Random desc',
                ParamOption::DEFAULT => null,
                ParamOption::EMPTY => false,
                ParamOption::MAX => null,
                ParamOption::MAX_LENGTH => null,
                ParamOption::MIN => null,
                ParamOption::MIN_LENGTH => null,
                ParamOption::OPTIONAL => false,
        ]);
    }
    
    /**
     * @test
     */
    public function test01() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'web-service'
        ], [
            'NewWeb2',
            "\n", // Hit Enter to pick default value (App\Apis)
            'get-hello-2',
            'Service\'s Desc',
            "\n", // Hit Enter to pick default value (GET method)
            'y',
            '6',
            'n',
            'y',
            'a-number',
            '3',
            'Random\'s desc',
            'n',
            "\n", // Hit Enter to pick default value
            "\n", // Hit Enter to pick default value
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\Apis'\n",
            "Enter a name for the new web service:\n",
            "Description:\n",
            "Request method:\n",
            "0: CONNECT\n",
            "1: DELETE\n",
            "2: GET <--\n",
            "3: HEAD\n",
            "4: OPTIONS\n",
            "5: PATCH\n",
            "6: POST\n",
            "7: PUT\n",
            "8: TRACE\n",
            "Would you like to add another request method?(y/N)\n",
            "Request method:\n",
            "0: CONNECT\n",
            "1: DELETE\n",
            "2: GET <--\n",
            "3: HEAD\n",
            "4: OPTIONS\n",
            "5: PATCH\n",
            "6: POST\n",
            "7: PUT\n",
            "8: TRACE\n",
            "Would you like to add another request method?(y/N)\n",
            "Would you like to add request parameters to the service?(y/N)\n",
            "Enter a name for the request parameter:\n",
            "Choose parameter type:\n",
            "0: array <--\n",
            "1: boolean\n",
            "2: email\n",
            "3: double\n",
            "4: integer\n",
            "5: json-obj\n",
            "6: string\n",
            "7: url\n",
            "Description:\n",
            "Is this parameter optional?(Y/n)\n",
            "Would you like to set minimum and maximum limites?(y/N)\n",
            "Success: New parameter added.\n",
            "Would you like to add another parameter?(y/N)\n",
            "Creating the class...\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'App'.DS."Apis\".\n",
            "Info: Don't forget to add the service to a services manager.\n",
        ], $output);
        
        $clazz = '\\App\\Apis\\NewWeb2Service';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass('\\App\\Apis\\NewWeb2Service');
        $instance = new $clazz();
        $instance instanceof AbstractWebService;
        $this->assertEquals('get-hello-2', $instance->getName());
        $this->assertEquals(1, count($instance->getParameters()));
        $this->assertEquals('Service\'s Desc', $instance->getDescription());
        $this->assertEquals([RequestMethod::GET, RequestMethod::POST], $instance->getRequestMethods());
        $param00 = $instance->getParameters()[0];
        $this->assertRequestParameter($param00, [
                ParamOption::NAME => 'a-number',
                ParamOption::TYPE => ParamType::DOUBLE,
                ParamOption::DESCRIPTION => 'Random\'s desc',
                ParamOption::DEFAULT => null,
                ParamOption::EMPTY => false,
                ParamOption::MAX => defined('PHP_FLOAT_MAX') ? PHP_FLOAT_MAX : 1.7976931348623E+308,
                ParamOption::MAX_LENGTH => null,
                ParamOption::MIN => defined('PHP_FLOAT_MIN') ? PHP_FLOAT_MIN : 2.2250738585072E-308,
                ParamOption::MIN_LENGTH => null,
                ParamOption::OPTIONAL => false,
        ]);
    }
    
    /**
     * @test
     */
    public function test02() {
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'web-service'
        ], [
            'NewWeb3',
            "\n", // Hit Enter to pick default value (App\Apis)
            'get-hello-3',
            'Service\'s Desc',
            "\n", // Hit Enter to pick default value (GET method)
            'y',
            '6',
            'n',
            'y',
            'a-number',
            '4',
            'Random\'s desc',
            'n',
            "\n", // Hit Enter to pick default value
            "\n", // Hit Enter to pick default value
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\Apis'\n",
            "Enter a name for the new web service:\n",
            "Description:\n",
            "Request method:\n",
            "0: CONNECT\n",
            "1: DELETE\n",
            "2: GET <--\n",
            "3: HEAD\n",
            "4: OPTIONS\n",
            "5: PATCH\n",
            "6: POST\n",
            "7: PUT\n",
            "8: TRACE\n",
            "Would you like to add another request method?(y/N)\n",
            "Request method:\n",
            "0: CONNECT\n",
            "1: DELETE\n",
            "2: GET <--\n",
            "3: HEAD\n",
            "4: OPTIONS\n",
            "5: PATCH\n",
            "6: POST\n",
            "7: PUT\n",
            "8: TRACE\n",
            "Would you like to add another request method?(y/N)\n",
            "Would you like to add request parameters to the service?(y/N)\n",
            "Enter a name for the request parameter:\n",
            "Choose parameter type:\n",
            "0: array <--\n",
            "1: boolean\n",
            "2: email\n",
            "3: double\n",
            "4: integer\n",
            "5: json-obj\n",
            "6: string\n",
            "7: url\n",
            "Description:\n",
            "Is this parameter optional?(Y/n)\n",
            "Would you like to set minimum and maximum limites?(y/N)\n",
            "Success: New parameter added.\n",
            "Would you like to add another parameter?(y/N)\n",
            "Creating the class...\n",
            'Info: New class was created at "'.ROOT_PATH.DS.'App'.DS."Apis\".\n",
            "Info: Don't forget to add the service to a services manager.\n",
        ], $output);
        
        $clazz = '\\App\\Apis\\NewWeb3Service';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
        $instance = new $clazz();
        $instance instanceof AbstractWebService;
        $this->assertEquals('get-hello-3', $instance->getName());
        $this->assertEquals(1, count($instance->getParameters()));
        $this->assertEquals('Service\'s Desc', $instance->getDescription());
        $this->assertEquals([RequestMethod::GET, RequestMethod::POST], $instance->getRequestMethods());
        $param00 = $instance->getParameters()[0];
        $this->assertRequestParameter($param00, [
                ParamOption::NAME => 'a-number',
                ParamOption::TYPE => ParamType::INT,
                ParamOption::DESCRIPTION => 'Random\'s desc',
                ParamOption::DEFAULT => null,
                ParamOption::EMPTY => false,
                ParamOption::MAX => PHP_INT_MAX,
                ParamOption::MAX_LENGTH => null,
                ParamOption::MIN => defined('PHP_INT_MIN') ? PHP_INT_MIN : ~PHP_INT_MAX,
                ParamOption::MIN_LENGTH => null,
                ParamOption::OPTIONAL => false,
        ]);
    }
    
    private function assertRequestParameter(RequestParameter $param, array $expected) {
        $this->assertEquals($expected[ParamOption::NAME], $param->getName());
        $this->assertEquals($expected[ParamOption::TYPE], $param->getType());
        $this->assertEquals($expected[ParamOption::DESCRIPTION], $param->getDescription());
        $this->assertEquals($expected[ParamOption::MIN_LENGTH], $param->getMinLength());
        $this->assertEquals($expected[ParamOption::MAX_LENGTH], $param->getMaxLength());
        $this->assertEquals($expected[ParamOption::MIN], $param->getMinValue());
        $this->assertEquals($expected[ParamOption::MAX], $param->getMaxValue());
        $this->assertEquals($expected[ParamOption::EMPTY], $param->isEmptyStringAllowed());
        $this->assertEquals($expected[ParamOption::OPTIONAL], $param->isOptional());
        $this->assertEquals($expected[ParamOption::DEFAULT], $param->getDefault());
    }
}
