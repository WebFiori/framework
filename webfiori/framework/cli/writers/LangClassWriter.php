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
namespace webfiori\framework\cli;

use webfiori\framework\cli\ClassWriter;

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
        $classInfoArr = [
            'name' => 'Language'.$langCode,
            'namespace' => "app\\langs",
            'path' => ROOT_DIR.DS.'app'.DS.'langs'
        ];
        parent::__construct($classInfoArr);

        $this->append("<?php\n");
        $this->append('namespace '.$this->getNamespace().";\n");
        $this->append("use webfiori\\framework\\i18n\\Language;");
        $this->append('');
        $this->append("/**\n"
                ." * A class which holds language information for the language which has code '$langCode'.");
        $this->append(" */");
        $this->append('class '.$this->getName().' extends Language {');

        $this->append("/**", 1);
        $this->append(" * Creates new instance of the class.", 1);
        $this->append(" */", 1);
        $this->append('public function __construct(){', 1);
        $this->append('parent::__construct(\''.$writingDir.'\', \''.$langCode.'\', true);', 2);
        $this->append('//TODO: Add the language "'.$langCode.'" labels.', 2);
        $this->append('}', 1);
        $this->append('}', 0);
    }
}
