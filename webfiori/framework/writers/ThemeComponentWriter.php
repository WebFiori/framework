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
namespace webfiori\framework\writers;

/**
 * A helper class which is used in generating theme template.
 *
 * @author Ibrahim
 */
class ThemeComponentWriter extends ClassWriter {
    private $classComment;
    private $extends;
    private $todo;
    public function __construct($extendsClass = 'HTMLNode', $comment = '', $todoTxt = '') {
        parent::__construct();
        $this->extends = $extendsClass;
        $this->classComment = $comment;
        $this->todo = $todoTxt;
    }
    public function getComment() {
        return $this->classComment;
    }
    public function getExtends() {
        return $this->extends;
    }
    public function getToDo() {
        return $this->todo;
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
    public function writeClassComment() {
        $extendsClass = $this->getExtends();
        $comment = $this->getComment();
        $this->append([
            'use WebFiori\\UI\\'.$extendsClass.';',
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
