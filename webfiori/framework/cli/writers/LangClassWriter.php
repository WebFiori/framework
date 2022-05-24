<?php

/**
 * MIT License
 *
 * Copyright (c) 2021 WebFiori framework
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace webfiori\framework\cli\writers;

use webfiori\framework\writers\ClassWriter;
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
        $this->addUseStatement('webfiori\\framework\\i18n\\Language');
    }

    public function writeClassBody() {
        $this->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
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
