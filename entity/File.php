<?php
/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
/**
 * A class that represents a file.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class File implements JsonI{
    /**
     * The size of the file in bytes.
     * @var int
     * @since 1.1 
     */
    private $fSize;
    /**
     * The full path to the file.
     * @var string 
     */
    private $path;
    /**
     * The name of the attachment.
     * @var string 
     * @since 1.0
     */
    private $fileName;
    /**
     * MIME type of the attachment (such as 'image/png')
     * @var string 
     * @since 1.0
     */
    private $mimeType;
    /**
     * A unique ID for the file.
     * @var string
     * @since 1.0 
     */
    private $id;
    /**
     * Raw data of the file in binary.
     * @var type 
     * @since 1.0
     */
    private $rawData;
    /**
     * Sets the value of the property <b>$path</b>.
     * @param string $path
     * @since 1.0
     */
    public function setPath($path){
        $this->path = $path;
    }
    /**
     * Gets the value of the property <b>$path</b>.
     * @return string
     * @since 1.0
     */
    public function getPath(){
        return $this->path;
    }
    public function setSize($size){
        if($size >= 0){
            $this->fSize = $size;
        }
    }
    public function getSize() {
        return $this->fSize;
    }
    /**
     * Sets the name of the file (such as 'my-image.png')
     * @param string $name The name of the file.
     * @since 1.0
     */
    public function setName($name){
        $this->fileName = $name;
    }
    /**
     * Returns the name of the file.
     * @return string The name of the file.
     * @since 1.0
     */
    public function getName(){
        return $this->fileName;
    }
    /**
     * Sets the MIME type of the file.
     * @param string $type MIME type (such as 'application/pdf')
     * @since 1.0
     */
    public function setMIMEType($type){
        $this->mimeType = $type;
    }
    /**
     * Returns MIME type of the file.
     * @return string MIME type.
     * @since 1.0
     */
    public function getMIMEType(){
        return $this->mimeType;
    }
    /**
     * Sets the ID of the file.
     * @param string $id The unique ID of the file.
     * @since 1.0
     */
    public function setID($id){
        $this->id = $id;
    }
    /**
     * Returns the ID of the file.
     * @return string The ID of the file.
     * @since 1.0
     */
    public function getID(){
        return $this->id;
    }
    /**
     * Sets the binary representation of the file.
     * @param type $raw Raw data of the file.
     * @since 1.0
     */
    public function setRawData($raw){
        $this->rawData = $raw;
    }
    /**
     * Returns the raw data of the file.
     * @return type Raw data of the file.
     * @since 1.0
     */
    public function getRawData(){
        return $this->rawData;
    }
    /**
     * Returns a JSON string that represents the file.
     * @return string A JSON string on the following format:<br/>
     * <b>{<br/>&nbsp;&nbsp;"id":"",<br/>&nbsp;&nbsp;"mime":"",<br/>&nbsp;&nbsp;"name":""<br/>}</b>
     * @since 1.0
     */
    public function toJSON(){
        $jsonX = new JsonX();
        $jsonX->add('id', $this->getID());
        $jsonX->add('mime', $this->getMIMEType());
        $jsonX->add('name', $this->getName());
        $jsonX->add('path', $this->getPath());
        $jsonX->add('size-in-bytes', $this->getSize());
        $jsonX->add('size-in-kbytes', $this->getSize()/8);
        $jsonX->add('size-in-mbytes', $this->getSize()/1000000);
        return $jsonX;
    }
    public function __toString() {
        return $this->toJSON().'';
    }
}

