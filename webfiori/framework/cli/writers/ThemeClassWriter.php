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
     */
    public function __construct($classNameInfo) {
        parent::__construct([
            'path' => $classNameInfo['path'],
            'namespace' => $classNameInfo['namespace'],
            'name' => $classNameInfo['name']
        ]);
        $this->append('<?php');
        $this->append('namespace '.$this->getNamespace().';');
        $this->append('');
        $this->append('use webfiori\\framework\\Theme;');
        $this->append('use webfiori\\ui\\HTMLNode;');
        $this->append('use '.$this->getNamespace().'\\AsideSection;');
        $this->append('use '.$this->getNamespace().'\\FooterSection;');
        $this->append('use '.$this->getNamespace().'\\HeadSection;');
        $this->append('use '.$this->getNamespace().'\\HeaderSection;');
        $this->append('');
        $this->append("class ".$this->getName().' extends Theme {');
        $this->append("/**", 1);
        $this->append(" * Creates new instance of the class.", 1);
        $this->append(" */", 1);
        $this->append('public function __construct(){', 1);
        $this->append('parent::__construct();', 2);
        $this->append('//TODO: Set the properties of your theme.', 2);

        $this->append('//$this->setName(\'Super Theme\');', 2);
        $this->append('//$this->setVersion(\'1.0\');', 2);
        $this->append('//$this->setAuthor(\'Me\');', 2);
        $this->append('//$this->setDescription(\'My Super Cool Theme.\');', 2);
        $this->append('//$this->setAuthorUrl(\'https://me.com\');', 2);
        $this->append('//$this->setLicenseName(\'MIT\');', 2);
        $this->append('//$this->setLicenseUrl(\'https://opensource.org/licenses/MIT\');', 2);
        $this->append('//$this->setCssDirName(\'css\');', 2);
        $this->append('//$this->setJsDirName(\'js\');', 2);
        $this->append('//$this->setImagesDirName(\'images\');', 2);

        $this->append('}', 1);

        $this->append('/**', 1);
        $this->append(" * Returns an object of type 'HTMLNode' that represents aside section of the page. ", 1);
        $this->append(' *', 1);
        $this->append(" * @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no aside", 1);
        $this->append(' * section, the method might return null.', 1);
        $this->append(' */', 1);
        $this->append('public function getAsideNode() {', 1);
        $this->append('return new AsideSection();', 2);
        $this->append('}', 1);
        $this->writeComponent('AsideSection', 'HTMLNode', 'A class that represents aside area of the theme.', 'Implement aside section of the theme.');

        $this->append('/**', 1);
        $this->append(" * Returns an object of type 'HTMLNode' that represents footer section of the page.", 1);
        $this->append(' *', 1);
        $this->append(" * @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no footer", 1);
        $this->append(' * section, the method might return null.', 1);
        $this->append(' */', 1);
        $this->append('public function getFooterNode() {', 1);
        $this->append('return new FooterSection();', 2);
        $this->append('}', 1);
        $this->writeComponent('FooterSection', 'HTMLNode', 'A class that represents footer section of the theme.', 'Implement footer section of the theme.');


        $this->append('/**', 1);
        $this->append(" * Returns an object of type HeadNode that represents HTML &lt;head&gt; node.", 1);
        $this->append(' *', 1);
        $this->append(" * @return HeadNode", 1);
        $this->append(' */', 1);
        $this->append('public function getHeadNode() {', 1);
        $this->append('return new HeadSection();', 2);
        $this->append('}', 1);
        $this->writeComponent('HeadSection', 'HeadNode', 'A class that represents "head" tag of the theme.', 'Include meta tags, CSS and JS files of the theme.');

        $this->append('/**', 1);
        $this->append(" * Returns an object of type HTMLNode that represents header section of the page.", 1);
        $this->append(' *', 1);
        $this->append(" * @return HTMLNode|null @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no header", 1);
        $this->append(' * section, the method might return null.', 1);
        $this->append(' */', 1);
        $this->append('public function getHeaderNode() {', 1);
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
        $writer->append('<?php');
        $writer->append('namespace '.$writer->getNamespace().';');
        $writer->append('');
        $writer->append('use webfiori\\ui\\'.$extends.';');
        $writer->append('/**');
        $writer->append('  * '.$classComment);
        $writer->append('  */');
        $writer->append("class ".$writer->getName().' extends '.$extends.' {');
        $writer->append("/**", 1);
        $writer->append(" * Creates new instance of the class.", 1);
        $writer->append(" */", 1);
        $writer->append('public function __construct(){', 1);

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
