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
namespace WebFiori\Framework\Writers;

use WebFiori\File\File;
use WebFiori\Framework\Theme;
use WebFiori\Ui\HeadNode;
use WebFiori\Ui\HTMLNode;

/**
 * A class which is used to create basic theme skeleton.
 *
 * @author Ibrahim
 */
class ThemeClassWriter extends ClassWriter {
    private $name;
    /**
     * Creates new instance of the class.
     *
     * @param array $classNameInfo An associative array that holds theme class
     * information. The array must have 3 indices, 'path', 'name' and 'namespace'.
     * The array can have additional indices that could hold theme info. The
     * indices are:
     * <ul>
     * <li>name: Holds theme name.</li>
     * </ul>
     */
    public function __construct(string $themeName = '') {
        parent::__construct('NewTheme', APP_PATH.'themes', APP_DIR.'\\themes\\new');

        if (!$this->setThemeName($themeName)) {
            $this->setThemeName('New Theme');
        }
        $this->setSuffix('Theme');
    }
    /**
     * Returns the name of the theme.
     *
     * @return string The method will return a string that represents the
     * name. Default return value is 'New Theme'.
     */
    public function getThemeName() : string {
        return $this->name;
    }
    /**
     * Removes the 4 classes that represents the components of the theme.
     */
    public function removeComponents() {
        $components = [
            $this->getNamespace().'\\AsideSection.php',
            $this->getNamespace().'\\FooterSection.php',
            $this->getNamespace().'\\HeadSection.php',
            $this->getNamespace().'\\HeaderSection.php',
        ];

        foreach ($components as $c) {
            $classFile = new File(ROOT_PATH.'\\'.$c);
            $classFile->remove();
        }
    }
    /**
     * Sets the name of the theme.
     *
     * @param string $name A non empty string that must be unique to the theme.
     *
     * @return bool If set, the method will return true. False otherwise.
     */
    public function setThemeName(string $name) : bool {
        $trimmed = trim($name);

        if (strlen($trimmed) > 0) {
            $this->name = $trimmed;

            return true;
        }

        return false;
    }

    public function writeClassBody() {
        $this->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
            $this->f('__construct')
        ], 1);
        $this->append([
            "parent::__construct('".$this->name."');",
            '//TODO: Set the properties of your theme.',
            '//$this->setName(\'Super Theme\');',
            '//$this->setVersion(\'1.0\');',
            '//$this->setAuthor(\'Me\');',
            '//$this->setDescription(\'My Super Cool Theme.\');',
            '//$this->setAuthorUrl(\'https://me.com\');',
            '//$this->setLicenseName(\'MIT\');',
            '//$this->setLicenseUrl(\'https://opensource.org/licenses/MIT\');',
            '//$this->setCssDirName(\'css\');',
            '//$this->setJsDirName(\'js\');',
            '//$this->setImagesDirName(\'images\');',
        ], 2);
        $this->append([
            '}',
            '/**',
            " * Returns an object of type 'HTMLNode' that represents aside section of the page. ",
            ' *',
            " * @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no aside",
            ' * section, the method might return null.',
            ' */',
            $this->f('getAsideNode', [], 'HTMLNode'),
        ], 1);
        $this->append('return new AsideSection();', 2);
        $this->append('}', 1);
        $this->writeComponent('AsideSection', 'HTMLNode', 'A class that represents aside area of the theme.', 'Implement aside section of the theme.');

        $this->append([
            '/**',
            " * Returns an object of type 'HTMLNode' that represents footer section of the page.",
            ' *',
            " * @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no footer",
            ' * section, the method might return null.',
            ' */',
            $this->f('getFooterNode', [], 'HTMLNode'),
        ], 1);
        $this->append('return new FooterSection();', 2);
        $this->append('}', 1);
        $this->writeComponent('FooterSection', 'HTMLNode', 'A class that represents footer section of the theme.', 'Implement footer section of the theme.');


        $this->append([
            '/**',
            " * Returns an object of type HeadNode that represents HTML &lt;head&gt; node.",
            ' *',

        ], 1);

        if (PHP_VERSION_ID <= 70333) {
            $this->append([
                " * @return HeadNode",
                ' */',
                $this->f('getHeadNode', [], 'HeadNode'),
            ], 1);
        } else {
            $this->append([
                " * @return HeadSection",
                ' */',
                $this->f('getHeadNode', [], 'HeadSection'),
            ], 1);
        }
        $this->append('return new HeadSection();', 2);
        $this->append('}', 1);
        $this->writeComponent('HeadSection', 'HeadNode', 'A class that represents "head" tag of the theme.', 'Include meta tags, CSS and JS files of the theme.');

        $this->append([
            '/**',
            " * Returns an object of type HTMLNode that represents header section of the page.",
            ' *',
            " * @return HTMLNode|null @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no header",
            ' * section, the method might return null.',
            ' */',
            $this->f('getHeaderNode', [], 'HTMLNode'),
        ], 1);
        $this->append('return new HeaderSection();', 2);
        $this->append('}', 1);
        $this->writeComponent('HeaderSection', 'HTMLNode', 'A class that represents the top section of the theme.', 'Add header components such as navigation links.');
        $this->append('}');
        $this->append('return __NAMESPACE__;');
    }

    public function writeClassComment() {
    }

    public function writeClassDeclaration() {
        $this->append("class ".$this->getName().' extends Theme {');
    }
    public function writeUseStatements() {
        parent::writeUseStatements();
        $this->addUseStatement([
            Theme::class,
            HeadNode::class
        ]);

        $this->addUseStatement([
            HTMLNode::class,
            $this->getNamespace().'\\AsideSection',
            $this->getNamespace().'\\FooterSection',
            $this->getNamespace().'\\HeadSection',
            $this->getNamespace().'\\HeaderSection',
        ]);
        $useArr = [];

        foreach ($this->getUseStatements() as $className) {
            $useArr[] = 'use '.$className.';';
        }
        $this->append($useArr);
    }
    private function writeComponent(string $className, string $extends, string $classComment, string $todoTxt) {
        $writer = new ThemeComponentWriter($extends, $classComment, $todoTxt);
        $writer->setPath($this->getPath());
        $writer->setNamespace($this->getNamespace());
        $writer->setClassName($className);
        $writer->writeClass();
    }
}
