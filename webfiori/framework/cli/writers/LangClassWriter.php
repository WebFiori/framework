<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2021 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\cli\writers;

use webfiori\framework\writers\ClassWriter;
use webfiori\framework\Language;
/**
 * A writer which is used to write any class that represents a language class.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 * 
 * @since 2.1
 */
class LangClassWriter extends ClassWriter {
    private $code;
    private $dir;
    /**
     * Creates new instance of the class.
     * 
     * @param string $langCode Language code such as 'AR' or 'EN'.
     * 
     * @param string $writingDir Writing direction of the language such as 
     * 'ltr' or 'rtl'.
     * 
     * @since 1.0
     */
    public function __construct($langCode, $writingDir) {
        parent::__construct('Language'.$langCode, ROOT_DIR.DS.APP_DIR_NAME.DS.'langs', APP_DIR_NAME."\\langs");
        $this->code = $langCode;
        $this->dir = $writingDir;
        $this->addUseStatement(Language::class);
    }

    public function writeClassBody() {
        $this->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
            $this->f('__construct'),
            'public function __construct() {'
        ], 1);

        $this->append([
            'parent::__construct(\''.$this->dir.'\', \''.$this->code.'\', true);',
            '//TODO: Add the language "'.$this->code.'" labels.'
        ], 2);
        $this->append('}', 1);
        $this->append('}', 0);
    }

    public function writeClassComment() {
        $this->append([
            "/**",
            " * A class which holds language information for the language which has code '$this->code'.",
            " */",
        ]);
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends Language {');
    }

}
