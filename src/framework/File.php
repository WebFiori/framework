<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\framework;

use webfiori\framework\exceptions\FileException;
use webfiori\http\Response;
use webfiori\json\Json;
use webfiori\json\JsonI;
/**
 * A class that represents a file.
 * 
 * This class can be used to read and write files in binary. In addition to that, 
 * it can be used to view files in web browsers.
 * 
 * @author Ibrahim
 * 
 * @version 1.1.8
 */
class File implements JsonI {
    /**
     * An associative array that contains MIME types of common files.
     * 
     * As of version 1.1.2 of the class, the array contains the 
     * following MIME types:
     * <ul>
     * <li><b>Audio and Video Formats:</b>
     * <ul>
     * <li>avi: video/avi</li>
     * <li>3gp: video/3gpp</li>
     * <li>ogv: video/ogg</li>
     * <li>mp4: video/mp4</li>
     * <li>mov: video/quicktime</li>
     * <li>wmv: video/x-ms-wmv</li>
     * <li>flv: video/x-flv</li>
     * <li>mpeg: video/mpeg</li>
     * <li>midi: audio/midi</li>
     * <li>oga: audio/ogg</li>
     * <li>mp3: audio/mpeg</li>
     * <li>mid: audio/midi</li>
     * <li>wav: audio/aac</li>
     * <li>acc: audio/aac</li>
     * </ul></li>
     * <li><b>Image Formats:</b>
     * <ul>
     * <li>jpeg: image/jpeg</li>
     * <li>jpg: image/jpeg</li>
     * <li>png: image/png</li>
     * <li>bmp: image/bmp</li>
     * <li>ico: image/x-icon</li>
     * <li>tiff: image/tiff</li>
     * <li>svg: image/svg+xml</li>
     * <li>psd: image/vnd.adobe.photoshop</li>
     * <li>gif: image/gif</li>
     * </ul></li>
     * <li><b>Documents Formats:</b>
     * <ul>
     * <li>pdf: application/pdf</li>
     * <li>rtf: application/rtf</li>
     * <li>doc: application/msword</li>
     * <li>docx: application/vnd.openxmlformats-officedocument.wordprocessingml.document</li>
     * <li>ppt: application/vnd.ms-powerpoint</li>
     * <li>pptx: application/vnd.openxmlformats-officedocument.presentationml.presentation</li>
     * <li>xls: application/vnd.ms-excel</li>
     * <li>xlsx: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet</li>
     * </ul></li>
     * <li><b>Text Based Formats:</b>
     * <ul>
     * <li>txt: text/plain</li>
     * <li>php: text/plain</li>
     * <li>log: text/plain</li>
     * <li>ini: text/plain</li>
     * <li>css: text/css</li>
     * <li>js: application/javascript</li>
     * <li>asm: text/x-asm</li>
     * <li>java: text/x-java-source</li>
     * <li>htaccess: application/x-extension-htaccess</li>
     * <li>asp: text/asp</li>
     * <li>c: text/x-c</li>
     * <li>cpp: text/x-c</li>
     * <li>csv: text/csv</li>
     * <li>htm: text/html</li>
     * <li>html: text/html</li>
     * </ul></li>
     * <li><b>Other Formats:</b>
     * <ul>
     * <li>sql: application/sql</li>
     * <li>jar: application/java-archive</li>
     * <li>zip: application/zip</li>
     * <li>rar: application/x-rar-compressed</li>
     * <li>tar: application/x-tar</li>
     * <li>7z: application/x-7z-compressed</li>
     * <li>exe: application/vnd.microsoft.portable-executable</li>
     * <li>bin: application/octet-stream</li>
     * <li>woff: font/woff</li>
     * <li>woff2: font/woff2</li>
     * <li>otf: font/otf</li>
     * <li>ttf: font/ttf</li>
     * <li>ai: application/postscript</li>
     * <li>swf: application/x-shockwave-flash</li>
     * <li>ogx: application/ogg</li>
     * </ul>
     * 
     * @since 1.1.1
     */
    const MIME_TYPES = [
        //audio and video
        'avi' => 'video/avi',
        '3gp' => 'video/3gpp',
        'mp4' => 'video/mp4',
        'mov' => 'video/quicktime',
        'wmv' => 'video/x-ms-wmv',
        'flv' => 'video/x-flv',
        'ogv' => 'video/ogg',
        'mpeg' => 'video/mpeg',
        'midi' => 'audio/midi',
        'mid' => 'audio/midi',
        'acc' => 'audio/aac',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'oga' => 'audio/ogg',
        //images 
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'ico' => 'image/x-icon',
        'tiff' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'psd' => 'image/vnd.adobe.photoshop',
        'gif' => 'image/gif',
        //pdf 
        'pdf' => 'application/pdf',
        //rich text format
        'rtf' => 'application/rtf',
        //MS office documents
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        //other text based files
        'txt' => 'text/plain',
        'php' => 'text/plain',
        'log' => 'text/plain',
        'ini' => 'text/plain',
        'css' => 'text/css',
        'js' => 'text/javascript',
        'asm' => 'text/x-asm',
        'java' => 'text/x-java-source',
        'htaccess' => 'application/x-extension-htaccess',
        'asp' => 'text/asp',
        'c' => 'text/x-c',
        'cpp' => 'text/x-c',
        'csv' => 'text/csv',
        'htm' => 'text/html',
        'html' => 'text/html',
        //other files
        'sql' => 'application/sql',
        'jar' => 'application/java-archive',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'tar' => 'application/x-tar',
        '7z' => 'application/x-7z-compressed',
        'exe' => 'application/vnd.microsoft.portable-executable',
        'bin' => 'application/octet-stream',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'otf' => 'font/otf',
        'ttf' => 'font/ttf',
        'ai' => 'application/postscript',
        'swf' => 'application/x-shockwave-flash',
        'ogx' => 'application/ogg'
    ];
    /**
     * The name of the attachment.
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $fileName;
    /**
     * The size of the file in bytes.
     * 
     * @var int
     * 
     * @since 1.1 
     */
    private $fileSize;
    /**
     * A unique ID for the file.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $id;
    /**
     * MIME type of the attachment (such as 'image/png')
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $mimeType;
    /**
     * The full path to the file.
     * 
     * @var string 
     */
    private $path;
    /**
     * Raw data of the file in binary.
     * 
     * @var type 
     * 
     * @since 1.0
     */
    private $rawData;
    /**
     * Creates new instance of the class.
     * 
     * This method will set the path and name to empty string. Also, it will 
     * set the size to 0 and ID to -1. Finally, it will set MIME type to 
     * "application/octet-stream"
     * 
     * @param string $fNameOrAbsPath The name of the file such as 'my-file.png'. 
     * This also can be the absolute path of the file (such as 'home/usr/ibrahim/my-file.png').
     * 
     * @param string|null $fPath The path of the file such as 'C:/Images/Test'. This can 
     * be null if absolute path of the file was provided for the first parameter.
     * 
     * @since 1.0
     */
    public function __construct($fNameOrAbsPath = '',$fPath = null) {
        $this->mimeType = 'application/octet-stream';

        if (!$this->setPath($fPath)) {
            $info = $this->_extractPathAndName($fNameOrAbsPath);
            $this->setDir($info['path']);
            $this->setName($info['name']);
        } else {
            $this->setName($fNameOrAbsPath);
        }

        $this->id = -1;
        $this->fileSize = 0;
    }
    /**
     * Returns JSON string that represents basic file info.
     * 
     * @return string
     */
    public function __toString() {
        return $this->toJSON().'';
    }
    /**
     * Returns the full path to the file.
     * 
     * The full path of the file is a string that contains the path of the 
     * file alongside its name. Assuming that the path is set to "C:/Users/Me/Documents" 
     * and file name is set to "my-doc.docx", This method will return something like 
     * "C:\Users\Me\Documents\my-do.docx".
     * 
     * @return string Full path to the file (e.g. 'root\images\hello.png').
     * If the name of the file is not set or the path is not set, the method 
     * will return empty string.
     * 
     * @since 1.1.1
     */
    public function getAbsolutePath() {
        $fPath = $this->getPath();
        $name = $this->getName();

        if (strlen($fPath) != 0 && strlen($name) != 0) {
            return $fPath.DS.$name;
        }

        return '';
    }
    /**
     * Returns the directory at which the file exist on.
     * 
     * The directory is simply the folder that contains the file. For example, 
     * the directory can be something like "C:\Users\Me\Documents". Note that the 
     * returned directory will be using backward slashes "\".
     * 
     * @return string The directory at which the file exist on.
     * 
     * @since 1.0
     */
    public function getDir() {
        return $this->getPath();
    }
    /**
     * Returns MIME type of the file.
     * 
     * Note that if the file is specified by its path and name, the method 
     * File::read() must be called before calling this method to update its 
     * MIME type.
     * 
     * @return string MIME type of the file. If MIME type of the file is not set 
     * or not detected, the method will return 'application/octet-stream'.
     * 
     * @since 1.0
     */
    public function getFileMIMEType() {
        return $this->mimeType;
    }
    /**
     * Returns the ID of the file.
     * 
     * This method is helpful in case the file is stored in database.
     * 
     * @return string The ID of the file. If the ID is not set, the method 
     * will return -1.
     * 
     * @since 1.0
     */
    public function getID() {
        return $this->id;
    }
    /**
     * Returns the time at which the file was last modified.
     * 
     * Note that this method will work only if the file exist in the file system.
     * 
     * @param string $format An optional format. The supported formats are the 
     * same formats which are supported by the function <code>date()</code>.
     * 
     * @return string|int If no format is provided, the method will return the 
     * time as integer. If a format is given, the method will return the time as 
     * specified by the format. If the file does not exist, the method will return 
     * 0.
     * 
     * @since 1.1.7
     */
    public function getLastModified($format = null) {
        if ($this->isExist()) {
            clearstatcache();

            if ($format !== null) {
                return date($format, filemtime($this->getAbsolutePath()));
            }

            return filemtime($this->getAbsolutePath());
        }

        return 0;
    }
    /**
     * Returns MIME type of a file type.
     * 
     * The method will try to find MIME type based on its extension. The method 
     * will look for MIME in the constant File::MIME_TYPES.
     * 
     * @param string $ext File extension without the suffix (such as 'jpg').
     * 
     * @return string|null If the extension MIME type is found, it will be 
     * returned. If not, the method will return null.
     * 
     * @since 1.1.1
     */
    public static function getMIMEType($ext) {
        $lowerCase = strtolower($ext);
        $retVal = null;

        //Fix for php 5.6: Cannot use isset() on the result of an expression
        $types = self::MIME_TYPES;

        if (isset($types[$lowerCase])) {
            $retVal = self::MIME_TYPES[$lowerCase];
        }

        return $retVal;
    }
    /**
     * Returns the name of the file.
     * 
     * The name is used to construct the absolute path of the file in addition 
     * to its path.
     * 
     * @return string The name of the file. If the name is not set, the method 
     * will return empty string.
     * 
     * @since 1.0
     */
    public function getName() {
        return $this->fileName;
    }
    /**
     * Returns the path of the file.
     * 
     * The path is simply the folder that contains the file. For example, 
     * the path can be something like "C:\Users\Me\Documents". Note that the 
     * returned path will be using backward slashes "\".
     * 
     * @return string The path to the file (such as "C:\Users\Me\Documents"). If 
     * the path is not set, the method will return empty string.
     * 
     * @since 1.0
     * 
     * @deprecated since version 1.1.5 Use File::getDir() instead.
     */
    public function getPath() {
        return $this->path;
    }
    /**
     * Returns the raw data of the file.
     * 
     * The raw data is simply a string. It can be binary string or any basic 
     * string.
     * 
     * @param string $encodeOrDecode This parameter is used to base-64 decode or 
     * encode file data. The parameter can have one of 3 values:
     * <ul>
     * <li>e: Encode the raw data of the file.</li>
     * <li>d: Decode the raw data of the file.</li>
     * <li>none: Return the raw data of the file as it is. This is the default value.</li>
     * </ul>
     * If any other value is given, the method will use 'none'.
     * 
     * @return string|null Raw data of the file. If no data is set, the method 
     * will return null.
     * 
     * @since 1.0
     */
    public function getRawData($encodeOrDecode = 'none') {
        $lower = strtolower(trim($encodeOrDecode));

        if ($this->rawData !== null) {
            if ($lower == 'e') {
                return base64_encode($this->rawData);
            } else if ($lower == 'd') {
                return base64_decode($this->rawData);
            }
        }

        return $this->rawData;
    }
    /**
     * Returns the size of the file in bytes.
     * 
     * Note that if the file is specified by its path and name, the method 
     * File::read() must be called before calling this method to update its 
     * size.
     * 
     * @return int Size of the file in bytes.
     */
    public function getSize() {
        return $this->fileSize;
    }
    /**
     * Checks if the file exist or not.
     * 
     * @return boolean If the file exist, the method will return true. Other than 
     * that, the method will return false.
     * 
     * @since 1.1.6
     */
    public function isExist() {
        return self::isFileExist($this->getAbsolutePath());
    }
    /**
     * Checks if file exist or not without throwing errors.
     * 
     * This method uses the function 'file_exists()' to check if a file is exist 
     * or not given its path. The only difference is that it will not 
     * throw an error if path is invalid.
     * 
     * @param string $path File path.
     * 
     * @since 1.1.8
     */
    public static function isFileExist($path) {
        set_error_handler(function ()
        {
        });
        $isExist = file_exists($path);
        restore_error_handler();

        return $isExist;
    }
    /**
     * Reads the file in binary mode.
     * 
     * First of all, this method checks the existence of the file. If it 
     * is exist, it tries to open the file in binary mode 'rb'. If a resource 
     * is created, it is used to read the content of the file. Also, the method 
     * will try to set MIME type of the file. If MIME type was not detected, 
     * it will set to 'application/octet-stream'. If the method is unable to 
     * read the file, it will throw an exception.
     * 
     * @param int $from The byte at which the method will start reading from. If -1 
     * is given, then the method will start reading from byte 0.
     * 
     * @param int $to The byte at which the method will read data to. If -1 
     * is given, then the method will read till last byte. Default is 
     * -1.
     * 
     * @throws FileException The method will throw an exception in 3 cases: 
     * <ul>
     * <li>If file name is not set.</li>
     * <li>If file path is not set.</li>
     * <li>If the file does not exist.</li>
     * </ul>
     */
    public function read($from = -1,$to = -1) {
        $fPath = $this->_checkNameAndPath();

        if (!$this->_readHelper($fPath,$from,$to)) {
            $fPath = str_replace('\\', '/', $this->getAbsolutePath());

            if (!$this->_readHelper($fPath,$from,$to)) {
                throw new FileException('File not found: \''.$fPath.'\'.');
            }
        }
    }
    /**
     * Removes a file given its name and path.
     * 
     * Before calling this method, the name of the file and its path must 
     * be specified.
     * 
     * @return boolean If the file was removed, the method will return 
     * true. Other than that, the method will return false.
     * 
     * @since 1.1.2
     */
    public function remove() {
        if ($this->isExist()) {
            unlink($this->getAbsolutePath());

            return true;
        }

        return false;
    }
    /**
     * Sets the name of the directory at which the file exist on.
     * 
     * The directory is simply the folder that contains the file. For example, 
     * the directory can be something like "C:/Users/Me/Documents". The directory can 
     * use forward slashes or backward slashes.
     * 
     * @param string $dir The directory which will contain the file. It must 
     * be non-empty string in order to set.
     * 
     * @return boolean The method will return true if the directory is set. Other 
     * than that, the method will return false.
     * 
     * @since 1.0
     */
    public function setDir($dir) {
        return $this->setPath($dir);
    }
    /**
     * Sets the ID of the file.
     * 
     * This method is helpful in case the file is stored in database.
     * 
     * @param string $id The unique ID of the file.
     * 
     * @since 1.0
     */
    public function setId($id) {
        $this->id = $id;
    }
    /**
     * Sets the MIME type of the file.
     * 
     * It is not recommended to update MIME type of the file manually. Only 
     * use this method for custom file types. MIME type will be set only 
     * if its non-empty string.
     * 
     * @param string $type MIME type (such as 'application/pdf')
     * 
     * @since 1.0
     */
    public function setMIMEType($type) {
        if (strlen($type) != 0) {
            $this->mimeType = $type;
        }
    }
    /**
     * Sets the name of the file (such as 'my-image.png')
     * 
     * The name is used to construct the absolute path of the file in addition 
     * to its path. The name of the file must include its extension (or suffix).
     * 
     * @param string $name The name of the file.
     * 
     * @since 1.0
     */
    public function setName($name) {
        $trimmed = trim($name);

        if (strlen($trimmed) != 0) {
            $this->fileName = $name;
            $this->_extractMimeFromName();
        }
    }
    /**
     * Sets the path of the file.
     * 
     * The path is simply the folder that contains the file. For example, 
     * the path can be something like "C:/Users/Me/Documents". The path can 
     * use forward slashes or backward slashes.
     * 
     * @param string $fPath The folder which will contain the file. It must 
     * be non-empty string in order to set.
     * 
     * @return boolean The method will return true if the path is set. Other 
     * than that, the method will return false.
     * 
     * @since 1.0
     * 
     * @deprecated since version 1.1.5 Use File::setDir() instead.
     */
    public function setPath($fPath) {
        $retVal = false;
        $pathV = self::_validatePath($fPath);
        $len = strlen($pathV);

        if ($len > 0) {
            $this->path = !Util::isDirectory($pathV) ? DS.$pathV : $pathV;
            $retVal = true;
        }

        return $retVal;
    }
    /**
     * Sets the binary representation of the file.
     * 
     * The raw data is simply a string. It can be binary string or any basic 
     * string. Also, it can be a blob which was retrieved from a database.
     * 
     * @param string $raw Binary raw data of the file.
     * 
     * @since 1.0
     */
    public function setRawData($raw) {
        if (strlen($raw) > 0) {
            $this->rawData = $raw;
            $this->_setSize(strlen($raw));
        }
    }
    /**
     * Returns a JSON string that represents the file.
     * 
     * @return Json An object of type 'Json' that contains file information. 
     * The object will have the following information:<br/>
     * <b>{<br/>&nbsp;&nbsp;"id":"",<br/>&nbsp;&nbsp;"mime":"",<br/>&nbsp;&nbsp;"name":"",<br/>
     * &nbsp;&nbsp;"path":"",<br/>&nbsp;&nbsp;"sizeInBytes":"",<br/>&nbsp;&nbsp;"sizeInKBytes":"",<br/>
     * &nbsp;&nbsp;"sizeInMBytes":""<br/>}</b>
     * 
     * @since 1.0
     */
    public function toJSON() {
        try {
            // This is used just to set the size of the file.
            $this->read();
        } catch (FileException $ex) {
        }

        return new Json([
            'id' => $this->getID(),
            'mime' => $this->getFileMIMEType(),
            'name' => $this->getName(),
            'directory' => $this->getDir(),
            'sizeInBytes' => $this->getSize(),
            'sizeInKBytes' => $this->getSize() / 1024,
            'sizeInMBytes' => ($this->getSize() / 1024) / 1024
        ]);
    }
    /**
     * Display the file. 
     * 
     * If the raw data of the file is null, the method will 
     * try to read the file that was specified by the name and its path. If 
     * the method is unable to read the file, an exception is thrown.
     * 
     * @param boolean $asAttachment If this parameter is set to 
     * true, the header 'content-disposition' will have the attribute 'attachment' 
     * set instead of 'inline'. This will trigger 'save as' dialog to appear.
     * 
     * @throws FileException An exception with the message "MIME type of raw data is not set." 
     * If MIME type of the file is not set.
     * 
     * @since 1.1.1
     */
    public function view($asAttachment = false) {
        $raw = $this->getRawData();

        if ($raw !== null) {
            $this->_viewFileHelper($asAttachment);
        } else {
            $this->read();
            $this->_viewFileHelper($asAttachment);
        }
    }
    /**
     * Write raw binary data into a file.
     * 
     * The method will write the data using the binary write mode. 
     * If it fails, It will throw an exception.
     * 
     * @param boolean $append If the file already exist in the file system and 
     * this attribute is set to true, the new raw data will be appended to the 
     * file. Default is true.
     * 
     * @param boolean $create If the file does not exist and this attribute is set 
     * to true, the method will attempt to create the file. Default is false.
     * 
     * @throws FileException The method will throw an exception in 3 cases: 
     * <ul>
     * <li>If file name is not set.</li>
     * <li>If file path is not set.</li>
     * <li>If the file does not exist and the parameter $create is set to false.</li>
     * </ul>
     * 
     * @since 1.1.1
     */
    public function write($append = true, $create = false) {
        $pathV = $this->_checkNameAndPath();
        $this->_writeHelper($pathV, $append === true, $create === true);
    }
    /**
     * 
     * @return string
     * 
     * @throws FileException
     */
    private function _checkNameAndPath() {
        clearstatcache();
        $fName = $this->getName();

        if (strlen($fName) != 0) {
            $fPath = $this->getPath();

            if (strlen($fPath) != 0) {
                return $this->getAbsolutePath();
            }
            throw new FileException('Path cannot be empty string.');
        }
        throw new FileException('File name cannot be empty string.');
    }
    private function _createResource($mode, $path) {
        set_error_handler(function()
        {
        });
        $resource = fopen($path, $mode);
        restore_error_handler();

        if (is_resource($resource)) {
            return $resource;
        }

        return false;
    }
    private function _extractMimeFromName() {
        $exp = explode('.', $this->getName());

        if (count($exp) > 1) {
            $ext = $exp[count($exp) - 1];
            $this->setMIMEType(self::getMIMEType($ext));
        }
    }
    private function _extractPathAndName($absPath) {
        $trimmed = trim($absPath);
        $cleanPath = str_replace('\\', DS, str_replace('/', DS, $trimmed));
        $pathArr = explode(DS, $cleanPath);

        if (count($pathArr) != 0) {
            $fPath = '';
            $name = $pathArr[count($pathArr) - 1];

            for ($x = 0 ; $x < count($pathArr) - 1 ; $x++) {
                $fPath .= $pathArr[$x].DS;
            }

            return [
                'path' => $fPath,
                'name' => $name
            ];
        }

        return [
            'name' => $trimmed,
            'path' => ''
        ];
    }
    private function _readHelper($fPath,$from,$to) {
        if ($this->isExist()) {
            $fSize = filesize($fPath);
            $this->_setSize($fSize);
            $bytesToRead = $to - $from > 0 ? $to - $from : $this->getSize();
            $resource = $this->_createResource('rb', $fPath);

            if (is_resource($resource)) {
                if ($bytesToRead > 0) {
                    fseek($resource, $from);
                }

                if ($bytesToRead > 0) {
                    $this->rawData = fread($resource, $bytesToRead);
                } else {
                    $this->rawData = '';
                }
                fclose($resource);
                $ext = pathinfo($this->getName(), PATHINFO_EXTENSION);
                $mime = self::getMIMEType($ext);
                $mimeSet = $mime === null ? 'application/octet-stream' : $mime;
                $this->setMIMEType($mimeSet);

                return true;
            }
            throw new FileException('Unable to open the file \''.$fPath.'\'.');
        } else {
            throw new FileException('File not found: \''.$fPath.'\'.');
        }
    }
    private function _setSize($size) {
        if ($size >= 0) {
            $this->fileSize = $size;
        }
    }
    private static function _validatePath($fPath) {
        $trimmedPath = trim($fPath);
        $len = strlen($trimmedPath);

        if ($len != 0) {
            while ($trimmedPath[$len - 1] == '/' || $trimmedPath[$len - 1] == '\\') {
                $tmpDir = trim($trimmedPath,'/');
                $trimmedPath = trim($tmpDir,'\\');
                $len = strlen($trimmedPath);
            }

            while ($trimmedPath[0] == '/' || $trimmedPath[0] == '\\') {
                $tmpDir = trim($trimmedPath,'/');
                $trimmedPath = trim($tmpDir,'\\');
            }
        }

        return str_replace('/', DS, str_replace('\\', DS, $trimmedPath));
    }
    private function _viewFileHelper($asAttachment) {
        $contentType = $this->getFileMIMEType();

        if ($contentType !== null) {
            Response::addHeader('Accept-Ranges', 'bytes');
            Response::addHeader('content-type', $contentType);

            if (isset($_SERVER['HTTP_RANGE'])) {
                $range = filter_var($_SERVER['HTTP_RANGE']);
                $rangeArr = explode('=', $range);
                $expl = explode('-', $rangeArr[1]);

                if (strlen($expl[1]) == 0) {
                    $expl[1] = $this->getSize();
                }
                $this->read($expl[0], $expl[1]);
                Response::setCode(206);
                Response::addHeader('content-range', 'bytes '.$expl[0].'-'.$expl[1].'/'.$this->getSize());
                Response::addHeader('content-length', $expl[1] - $expl[0]);
            } else {
                Response::addHeader('Content-Length', $this->getSize());
            }

            if ($asAttachment === true) {
                Response::addHeader('Content-Disposition', 'attachment; filename="'.$this->getName().'"');
            } else {
                Response::addHeader('Content-Disposition', 'inline; filename="'.$this->getName().'"');
            }
            Response::write($this->getRawData());
        } else {
            throw new FileException('MIME type of raw data is not set.');
        }
    }
    /**
     * 
     * @param string $fPath
     * @param boolean $append
     * @param boolean $createIfNotExist
     * @return boolean
     * @throws FileException
     */
    private function _writeHelper($fPath, $append = true, $createIfNotExist = false) {
        if (!$this->isExist()) {
            if ($createIfNotExist) {
                $resource = $this->_createResource('wb', $fPath);
            } else {
                throw new FileException("File not found: '$fPath'.");
            }
        } else if ($append) {
            $resource = $this->_createResource('ab', $fPath);
        } else {
            $resource = $this->_createResource('rb+', $fPath);
        }

        if (!is_resource($resource)) {
            throw new FileException('Unable to open the file at \''.$fPath.'\'.');
        } else {
            fwrite($resource, $this->getRawData());
            fclose($resource);

            return true;
        }
    }
}
