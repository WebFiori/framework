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
 * A fluent builder for generating PHPDoc blocks.
 *
 * @author Ibrahim
 */
class DocblockBuilder {
    private $writer;
    private $description;
    private $params = [];
    private $return;
    private $tags = [];
    
    /**
     * Creates new instance of the builder.
     *
     * @param ClassWriter $writer The class writer instance
     * @param string $description Main description text
     */
    public function __construct(ClassWriter $writer, string $description = '') {
        $this->writer = $writer;
        $this->description = $description;
    }
    
    /**
     * Add a parameter to the docblock.
     *
     * @param string $type Parameter type
     * @param string $name Parameter name (without $)
     * @param string $desc Optional description
     * 
     * @return DocblockBuilder
     */
    public function param(string $type, string $name, string $desc = '') : self {
        $this->params[] = ['type' => $type, 'name' => $name, 'desc' => $desc];
        return $this;
    }
    
    /**
     * Add a return tag to the docblock.
     *
     * @param string $type Return type
     * @param string $desc Optional description
     * 
     * @return DocblockBuilder
     */
    public function returns(string $type, string $desc = '') : self {
        $this->return = ['type' => $type, 'desc' => $desc];
        return $this;
    }
    
    /**
     * Add a custom tag to the docblock.
     *
     * @param string $name Tag name (without @)
     * @param string $value Optional tag value
     * 
     * @return DocblockBuilder
     */
    public function tag(string $name, string $value = '') : self {
        $this->tags[] = ['name' => $name, 'value' => $value];
        return $this;
    }
    
    /**
     * Add @throws tag.
     *
     * @param string $exception Exception class name
     * @param string $desc Optional description
     * 
     * @return DocblockBuilder
     */
    public function throws(string $exception, string $desc = '') : self {
        return $this->tag('throws', $exception . ($desc ? ' ' . $desc : ''));
    }
    
    /**
     * Add @deprecated tag.
     *
     * @param string $message Optional deprecation message
     * 
     * @return DocblockBuilder
     */
    public function deprecated(string $message = '') : self {
        return $this->tag('deprecated', $message);
    }
    
    /**
     * Add @since tag.
     *
     * @param string $version Version number
     * 
     * @return DocblockBuilder
     */
    public function since(string $version) : self {
        return $this->tag('since', $version);
    }
    
    /**
     * Build and append the docblock to the class writer.
     *
     * @param int $indent Indentation level (number of tabs)
     * 
     * @return array The generated docblock lines
     */
    public function build(int $indent = 1) : array {
        $lines = ['/**'];
        
        if ($this->description) {
            foreach (explode("\n", $this->description) as $line) {
                $lines[] = ' * ' . $line;
            }
            if (!empty($this->params) || $this->return || !empty($this->tags)) {
                $lines[] = ' *';
            }
        }
        
        foreach ($this->params as $param) {
            $line = ' * @param ' . $param['type'] . ' $' . $param['name'];
            if ($param['desc']) {
                $line .= ' ' . $param['desc'];
            }
            $lines[] = $line;
        }
        
        if ($this->return) {
            $line = ' * @return ' . $this->return['type'];
            if ($this->return['desc']) {
                $line .= ' ' . $this->return['desc'];
            }
            $lines[] = $line;
        }
        
        foreach ($this->tags as $tag) {
            $line = ' * @' . $tag['name'];
            if ($tag['value']) {
                $line .= ' ' . $tag['value'];
            }
            $lines[] = $line;
        }
        
        $lines[] = ' */';
        
        $this->writer->append($lines, $indent);
        return $lines;
    }
}
