<?php
namespace webfiori\framework\cli;

use webfiori\cli\CLICommand;
use webfiori\database\ConnectionInfo;
use webfiori\database\Table;
use webfiori\framework\WebFioriApp;
use webfiori\framework\writers\ClassWriter;
/**
 * A class which is used to hold common CLI methods for reading inputs.
 *
 * @author Ibrahim
 */
class CLIUtils {
    /**
     * Reads the name of database table information and returns an instance of
     * it.
     * 
     * This method is used to get table class name from the argument '--table'.
     * If the argument is not specified or invalid, the method will prompt the 
     * user to enter class name.
     * 
     * @param CLICommand $c The command which is used to read inputs and send
     * outputs. 
     * 
     * @return Table The method will return an instance of the class if
     * successfully created.
     */
    public static function readTable(CLICommand $c) : Table {
        $tableClassNameValidity = false;
        $tableClassName = $c->getArgValue('--table');
        $tableObj = null;
        
        do {
            if ($tableClassName === null || strlen($tableClassName) == 0) {
                $tableClassName = $c->getInput('Enter database table class name (include namespace):');
            }

            if (!class_exists($tableClassName)) {
                $c->error('Class not found.');
                $tableClassName = '';
                continue;
            }
            $tableObj = new $tableClassName();

            if (!$tableObj instanceof Table) {
                $c->error('The given class is not a child of the class "'.Table::class.'".');
                $tableClassName = '';
                continue;
            }
            $tableClassNameValidity = true;
        } while (!$tableClassNameValidity);
        
        return $tableObj;
    }
    /**
     * Reads and validates class name.
     * 
     * @param CLICommand $c The command that will be used to read the input from.
     * 
     * @param string|null $suffix An optional string to append to class name.
     * 
     * @param string $prompt The text that will be shown to the user as prompt for
     * class name.
     * 
     * @return string A string that represents a valid class name.
     */
    public static function readName(CLICommand $c, string $suffix = null, string $prompt = 'Enter class name:') : string {
        $isNameValid = false;

        do {
            $className = trim($c->getInput($prompt));
            
            if ($suffix !== null) {
                $subSuffix = substr($className, strlen($className) - strlen($suffix));
                
                if ($subSuffix != $suffix) {
                    $className .= $suffix;
                }
            }
            
            $isNameValid = ClassWriter::isValidClassName($className);

            if (!$isNameValid) {
                $c->error('Invalid class name is given.');
            }
        } while (!$isNameValid);

        return $className;
    }
    /**
     * Reads and validates class namespace.
     * 
     * @param CLICommand $c The command that will be used to read the input from.
     * 
     * @param string $defaultNs An optional string that will be used as default
     * namespace if no input is provided.
     * 
     * @param string $prompt The text that will be shown to the user as prompt for
     * the namespace.
     * 
     * @return string A validated string that represents a namespace.
     */
    public static function readNamespace(CLICommand $c, string $defaultNs = '\\', $prompt = 'Enter class namespace:') : string {
        $isNameValid = false;

        do {
            $ns = str_replace('/','\\',trim($c->getInput($prompt, $defaultNs)));
            $isNameValid = ClassWriter::isValidNamespace($ns);

            if (!$isNameValid) {
                $c->error('Invalid namespace is given.');
            }
        } while (!$isNameValid);

        return trim($ns,'\\');
    }
    /**
     * Select database connection and return its name as string.
     * 
     * This method is used to get connection name from the argument '--connection'.
     * If the argument is not specified, the method will prompt the user to select
     * connection based on the connections stored in the class 'AppConfig' of
     * the application. If no connections are stored in the class 'AppConfig',
     * the method will simply return null.
     * 
     * @param CLICommand $c The command which is used to read inputs and send
     * outputs.
     * 
     * @return ConnectionInfo|null If a connection was found, the method will return it's
     * information as an object of type 'ConnectionInfo'. Other than that null is returned.
     */
    public static function getConnectionName(CLICommand $c) {
        $connName = $c->getArgValue('--connection');
        $dbConnections = array_keys(WebFioriApp::getAppConfig()->getDBConnections());
        
        if (count($dbConnections) == 0) {
            $c->warning('No database connections found in the class "'.APP_DIR_NAME.'\\AppConfig"!');
            $c->info('Run the command "add" to add connections.');
            
            return null;
        }
        
        if (in_array($connName, $dbConnections)) {
            return $connName;
        } else if ($connName !== null) {
            $c->error('No connection with name "'.$connName.'" was found!');
        }
        
        $name = $c->select('Select database connection:', $dbConnections, 0);
        
        return WebFioriApp::getAppConfig()->getDBConnection($name);
    }
}
