<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Writers;

/**
 * Writer for creating annotation-based REST services.
 *
 * @author Ibrahim
 */
class RestServiceWriter extends ClassWriter {
    private $description;
    private $methods = [];
    
    public function __construct() {
        parent::__construct('NewService', APP_PATH.'Apis', APP_DIR.'\\Apis');
        $this->setSuffix('Service');
        $this->addUseStatement([
            'WebFiori\\Http\\WebService',
            'WebFiori\\Http\\Annotations\\RestController',
            'WebFiori\\Http\\Annotations\\GetMapping',
            'WebFiori\\Http\\Annotations\\PostMapping',
            'WebFiori\\Http\\Annotations\\PutMapping',
            'WebFiori\\Http\\Annotations\\DeleteMapping',
            'WebFiori\\Http\\Annotations\\Param',
            'WebFiori\\Http\\Annotations\\ResponseBody',
            'WebFiori\\Http\\Annotations\\AllowAnonymous',
            'WebFiori\\Http\\ParamType'
        ]);
    }
    
    public function setDescription(string $desc) {
        $this->description = $desc;
    }
    
    public function addMethod(string $httpMethod, string $methodName, array $params = [], string $returnType = 'array') {
        $this->methods[] = [
            'http' => $httpMethod,
            'name' => $methodName,
            'params' => $params,
            'return' => $returnType
        ];
    }
    
    public function writeClassBody() {
        foreach ($this->methods as $method) {
            $this->writeMethod($method);
        }
        $this->append('}');
    }
    
    public function writeClassComment() {
        $serviceName = strtolower(str_replace('Service', '', $this->getName()));
        $this->append('/**');
        $this->append(' * '.$this->description);
        $this->append(' */');
        $this->append("#[RestController('$serviceName', '{$this->description}')]", 0);
    }
    
    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends WebService {');
    }
    
    private function writeMethod(array $method) {
        $this->append('', 1);
        $mapping = ucfirst(strtolower($method['http'])).'Mapping';
        $this->append("#[$mapping]", 1);
        $this->append('#[ResponseBody]', 1);
        $this->append('#[AllowAnonymous]', 1);
        
        foreach ($method['params'] as $param) {
            $paramAttr = "#[Param('{$param['name']}', ParamType::{$param['type']}, '{$param['description']}'";
            if (isset($param['min'])) {
                $paramAttr .= ", min: {$param['min']}";
            }
            if (isset($param['max'])) {
                $paramAttr .= ", max: {$param['max']}";
            }
            $paramAttr .= ')]';
            $this->append($paramAttr, 1);
        }
        
        $signature = 'public function '.$method['name'].'(';
        $paramList = [];
        foreach ($method['params'] as $param) {
            $type = $this->mapParamType($param['type']);
            $paramList[] = "?$type \${$param['name']} = null";
        }
        $signature .= implode(', ', $paramList);
        $signature .= '): '.$method['return'].' {';
        
        $this->append($signature, 1);
        $this->append('// TODO: Implement method logic', 2);
        $this->append('return [];', 2);
        $this->append('}', 1);
    }
    
    private function mapParamType(string $type): string {
        return match($type) {
            'INT' => 'int',
            'STRING', 'EMAIL', 'URL' => 'string',
            'DOUBLE' => 'float',
            'BOOL' => 'bool',
            'ARRAY' => 'array',
            default => 'string'
        };
    }
}
