<?php
namespace webfiori\framework\cli\writers;

/**
 * Description of ThemeClassWriter
 *
 * @author Ibrahim
 */
class ThemeClassWriter extends ClassWriter {
    public function __construct($themePath, $className) {
        parent::__construct([
            'path' => THEMES_PATH.DS.$themePath,
            'namespace' => 'themes\\'.$themePath,
            'name' => $className
        ]);
        $this->append('<?php');
        $this->append('namespace '.$this->getNamespace().';');
        $this->append('');
        $this->append('use webfiori\\framework\\Theme;');
        $this->append('use webfiori\ui\HTMLNode;');
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
        $this->append('$asideNode = new HTMLNode();', 2);
        $this->append('return $asideNode;', 2);
        $this->append('}', 1);

        $this->append('/**', 1);
        $this->append(" * Returns an object of type 'HTMLNode' that represents footer section of the page.", 1);
        $this->append(' *', 1);
        $this->append(" * @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no footer", 1);
        $this->append(' * section, the method might return null.', 1);
        $this->append(' */', 1);
        $this->append('public function getFooterNode() {', 1);
        $this->append('$footerNode = new HTMLNode();', 2);
        $this->append('return $footerNode;', 2);
        $this->append('}', 1);

        $this->append('/**', 1);
        $this->append(" * Returns an object of type HeadNode that represents HTML &lt;head&gt; node.", 1);
        $this->append(' *', 1);
        $this->append(" * @return HeadNode", 1);
        $this->append(' */', 1);
        $this->append('public function getHeadNode() {', 1);
        $this->append('$headNode = new HeadNode();', 2);
        $this->append('return $headNode;', 2);
        $this->append('}', 1);

        $this->append('/**', 1);
        $this->append(" * Returns an object of type HTMLNode that represents header section of the page.", 1);
        $this->append(' *', 1);
        $this->append(" * @return HTMLNode|null @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no header", 1);
        $this->append(' * section, the method might return null.', 1);
        $this->append(' */', 1);
        $this->append('public function getHeadrNode() {', 1);
        $this->append('$headerNode = new HTMLNode();', 2);
        $this->append('return $headerNode;', 2);
        $this->append('}', 1);

        $this->append('}');
        $this->append('return __NAMESPACE__;');
    }
}
