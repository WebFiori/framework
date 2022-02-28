<?php
namespace webfiori\framework\cli\writers;

use webfiori\framework\cli\writers\ClassWriter;
/**
 * A helper class which is used in generating theme template.
 *
 * @author Ibrahim
 */
class ThemeComponentWriter extends ClassWriter {
    private $extends;
    private $classComment;
    private $todo;
    public function __construct($classInfoArr = [], $extendsClass = 'HTMLNode', $comment = '', $todoTxt = '') {
        parent::__construct($classInfoArr);
        $this->extends = $extendsClass;
        $this->classComment = $comment;
        $this->todo = $todoTxt;
    }
    public function getToDo() {
        return $this->todo;
    }
    public function getComment() {
        return $this->classComment;
    }
    public function writeClassBody() {
        $this->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
            'public function __construct(){',
        ], 1);
        $extendsClass = $this->getExtends();
        if ($extendsClass != 'HeadNode') {
            $this->append('parent::__construct(\'div\');', 2);
        } else {
            $this->append('parent::__construct();', 2);
        }
        $this->append('//TODO: '.$this->getToDo(), 2);
        $this->append('}', 1);
        $this->append('}');
    }
    public function getExtends() {
        return $this->extends;
    }
    public function writeClassComment() {
        $extendsClass = $this->getExtends();
        $comment = $this->getComment();
        $this->append([
            'use webfiori\\ui\\'.$extendsClass.';',
            '',
            '/**',
            '  * '.$comment,
            '  */',
        ]);
        
    }

    public function writeClassDeclaration() {
        $this->append("class ".$this->getName().' extends '.$this->getExtends().' {');
    }

}
