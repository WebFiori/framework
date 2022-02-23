<?php
namespace webfiori\framework\cli\writers;

/**
 * Description of ThemeClassWriter
 *
 * @author Ibrahim
 */
class ThemeClassWriter extends ClassWriter {
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
    public function __construct($classNameInfo) {
        parent::__construct([
            'path' => $classNameInfo['path'],
            'namespace' => $classNameInfo['namespace'],
            'name' => $classNameInfo['name']
        ]);
        $this->appendTop();
        $this->append([
            'use webfiori\\framework\\Theme;',
            'use webfiori\\ui\\HTMLNode;',
            'use '.$this->getNamespace().'\\AsideSection;',
            'use '.$this->getNamespace().'\\FooterSection;',
            'use '.$this->getNamespace().'\\HeadSection;',
            'use '.$this->getNamespace().'\\HeaderSection;',
            '',
            "class ".$this->getName().' extends Theme {'
        ]);
        $this->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
            'public function __construct() {'
        ], 1);
        $name = isset($classNameInfo['name']) ? "'".$classNameInfo['name']."'" : null;
        $this->append([
            "parent::__construct($name);",
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
            'public function getAsideNode() {', 
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
            'public function getFooterNode() {',
        ], 1);
        $this->append('return new FooterSection();', 2);
        $this->append('}', 1);
        $this->writeComponent('FooterSection', 'HTMLNode', 'A class that represents footer section of the theme.', 'Implement footer section of the theme.');


        $this->append([
            '/**',
            " * Returns an object of type HeadNode that represents HTML &lt;head&gt; node.",
            ' *',
            " * @return HeadNode",
            ' */',
            'public function getHeadNode() {',
        ], 1);
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
            'public function getHeaderNode() {',
        ], 1);
        $this->append('return new HeaderSection();', 2);
        $this->append('}', 1);
        $this->writeComponent('HeaderSection', 'HTMLNode', 'A class that represents the top section of the theme.', 'Add header components such as navigation links.');
        $this->append('}');
        $this->append('return __NAMESPACE__;');
    }
    private function writeComponent($className, $extends, $classComment, $todoTxt) {
        $writer = new ClassWriter([
            'path' => $this->getPath(),
            'namespace' => $this->getNamespace(),
            'name' => $className
        ]);
        $writer->appendTop();
        $writer->append([
            'use webfiori\\ui\\'.$extends.';',
            '',
            '/**',
            '  * '.$classComment,
            '  */',
            "class ".$writer->getName().' extends '.$extends.' {'
        ]);
        $writer->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
            'public function __construct(){',
        ], 1);

        if ($extends != 'HeadNode') {
            $writer->append('parent::__construct(\'div\');', 2);
        } else {
            $writer->append('parent::__construct();', 2);
        }
        $writer->append('//TODO: '.$todoTxt, 2);
        $writer->append('}', 1);
        $writer->append('}');
        $writer->writeClass();
    }
}
