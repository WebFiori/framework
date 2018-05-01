<?php
/**
 * A class that represents a file.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class File implements JsonI{
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
        return $jsonX;
    }
}

