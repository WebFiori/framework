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
require_once '../root.php';
/**
 * An API that has functionality to do with numbers.
 *
 * @author Ibrahim
 * @version 1.0
 */
class NumsAPIs extends API{
    /**
     * The default number of bytes in any returned result.
     * @var int The default number of bytes in any returned result.
     * @since 1.0
     */
    private $defaultBytesCount;
    public function __construct() {
        parent::__construct();
        $this->defaultBytesCount = 4;
        $this->setVersion('1.0.0');
        $a1 = new APIAction();
        $a1->setName('int-to-binary');
        $a1->setDescription('Converts an integer to a binary number in 2\'s complement.');
        $a1->addRequestMethod('get');
        $a1->addParameter(new RequestParameter('integer', 'integer'));
        $a1->getParameterByName('integer')->setDescription('The integer that will be converted.');
        $a1->addParameter(new RequestParameter('bytes', 'integer',TRUE));
        $a1->getParameterByName('bytes')->setDescription('Optional paameter. The number of bytes '
                . 'that the binary number will contains. Default is 4. If the '
                . 'given number is less than the number of bytes in the converted number, '
                . 'Default is used. Minimum value is 1 byte and maximum is 16 bytes.');
        $a1->getParameterByName('bytes')->setMaxVal(16);
        $a1->getParameterByName('bytes')->setMinVal(1);
        $a1->getParameterByName('bytes')->setDefault(4);
        $this->addAction($a1);
        
        $a2 = new APIAction();
        $a2->setName('int-to-hex');
        $a2->addRequestMethod('get');
        $a2->setDescription('Converts an integer to a headecimal number in 2\'s complement.');
        $a2->addRequestMethod('get');
        $a2->addParameter(new RequestParameter('integer', 'integer'));
        $a2->getParameterByName('integer')->setDescription('The integer that will be converted.');
        $a2->addParameter(new RequestParameter('bytes', 'integer',TRUE));
        $a2->getParameterByName('bytes')->setDescription('Optional paameter. The number of bytes '
                . 'that the binary number will contains. Default is 4. If the '
                . 'given number is less than the number of bytes in the converted number, '
                . 'Default is used. Minimum value is 1 byte and maximum is 16 bytes.');
        $a2->getParameterByName('bytes')->setMaxVal(16);
        $a2->getParameterByName('bytes')->setMinVal(1);
        $a2->getParameterByName('bytes')->setDefault(4);
        $this->addAction($a2);
        
        $a3 = new APIAction();
        $a3->setName('binary-to-int');
        $a3->setDescription('Converts a binary number given in in 2\'s complement to its integer value.');
        $a3->addRequestMethod('get');
        $a3->addParameter(new RequestParameter('binary', 'string'));
        $a3->getParameterByName('binary')->setDescription('The binary number as string. It must be in two\'s complement representation.');
        $this->addAction($a3);
        
        $a4 = new APIAction();
        $a4->setName('binary-to-hex');
        $a4->setDescription('Converts a binary number given in in 2\'s complement to its hexadecimal representation.');
        $a4->addRequestMethod('get');
        $a4->addParameter(new RequestParameter('binary', 'string'));
        $a4->getParameterByName('binary')->setDescription('The binary number as string. It must be in two\'s complement representation.');
        $a4->addParameter(new RequestParameter('bytes', 'integer',TRUE));
        $a4->getParameterByName('bytes')->setDescription('Optional paameter. The number of bytes '
                . 'that the binary number will contains. Default is 4. If the '
                . 'given number is less than the number of bytes in the converted number, '
                . 'Default is used. Minimum value is 1 byte and maximum is 16 bytes.');
        $a4->getParameterByName('bytes')->setMaxVal(16);
        $a4->getParameterByName('bytes')->setMinVal(1);
        $a4->getParameterByName('bytes')->setDefault(4);
        $this->addAction($a4);
        
        $a5 = new APIAction();
        $a5->setName('hex-to-int');
        $a5->setDescription('Converts a hexadecimal number given in in 2\'s complement to its integer value.');
        $a5->addRequestMethod('get');
        $a5->addParameter(new RequestParameter('hex', 'string'));
        $this->addAction($a5);
        
        $a6 = new APIAction();
        $a6->setName('hex-to-binary');
        $a6->setDescription('Converts a hexadecimal number given in in 2\'s complement to its binary representation.');
        $a6->addRequestMethod('get');
        $a6->addParameter(new RequestParameter('hex', 'string'));
        $a6->getParameterByName('hex')->setDescription('The hexadecimal number as string. It must be in two\'s complement representation.');
        $a6->addParameter(new RequestParameter('bytes', 'integer',TRUE));
        $a6->getParameterByName('bytes')->setDescription('Optional paameter. The number of bytes '
                . 'that the binary number will contains. Default is 4. If the '
                . 'given number is less than the number of bytes in the converted number, '
                . 'Default is used. Minimum value is 1 byte and maximum is 16 bytes.');
        $a6->getParameterByName('bytes')->setMaxVal(16);
        $a6->getParameterByName('bytes')->setMinVal(1);
        $a6->getParameterByName('bytes')->setDefault(4);
        $this->addAction($a6);
        
        $a7 = new APIAction();
        $a7->setName('binary-add');
        $a7->setDescription('Adds two binary numbers.');
        $a7->addRequestMethod('get');
        $a7->addParameter(new RequestParameter('binary-1', 'string'));
        $a7->getParameterByName('binary-1')->setDescription('The first binary number given in two\'s complement representation.');
        $a7->addParameter(new RequestParameter('binary-2', 'string'));
        $a7->getParameterByName('binary-2')->setDescription('The second binary number given in two\'s complement representation.');
        $this->addAction($a7);
        
        $a8 = new APIAction();
        $a8->setName('binary-sub');
        $a8->setDescription('Subtracts two binary numbers.');
        $a8->addRequestMethod('get');
        $a8->addParameter(new RequestParameter('binary-1', 'string'));
        $a8->getParameterByName('binary-1')->setDescription('The first binary number given in two\'s complement representation.');
        $a8->addParameter(new RequestParameter('binary-2', 'string'));
        $a8->getParameterByName('binary-2')->setDescription('The second binary number given in two\'s complement representation.');
        $this->addAction($a8);
        
        $a9 = new APIAction();
        $a9->setName('hex-add');
        $a9->setDescription('Adds two hexadecimal numbers.');
        $a9->addRequestMethod('get');
        $a9->addParameter(new RequestParameter('hex-1', 'string'));
        $a9->getParameterByName('hex-1')->setDescription('The first hexadecimal number given in two\'s complement representation.');
        $a9->addParameter(new RequestParameter('hex-2', 'string'));
        $a9->getParameterByName('hex-2')->setDescription('The second hexadecimal number given in two\'s complement representation.');
        $this->addAction($a9);
        
        $a10 = new APIAction();
        $a10->setName('hex-sub');
        $a10->setDescription('Subtracts two hexadecimal numbers.');
        $a10->addRequestMethod('get');
        $a10->addParameter(new RequestParameter('hex-1', 'string'));
        $a10->getParameterByName('hex-1')->setDescription('The first hexadecimal number given in two\'s complement representation.');
        $a10->addParameter(new RequestParameter('hex-2', 'string'));
        $a10->getParameterByName('hex-2')->setDescription('The second hexadecimal number given in two\'s complement representation.');
        $this->addAction($a10);
    }
    
    public function isAuthorized() {
        return TRUE;
    }
    
    private function hexToBinary(){
        $defBits = $this->defaultBytesCount*8;
        $hex = $this->getInputs()['hex'];
        $bytes = $this->getInputs()['bytes'];
        $j = new JsonX();
        $j->add('as-binary', '');
        $j->add('as-hex', '');
        $j->add('bits', 0);
        $j->add('bytes', 0);
        $asBinary = Num::hexToBinary($hex);
        if($asBinary != Num::INV_HEX){
            $len = strlen($asBinary);
            if($bytes*8 > $len){
                $binaryExt = Num::binaryExtend($asBinary,$bytes*8 - $len, $asBinary[0]);
            }
            else{
                $j->add('bytes', $defBits/8);
                $binaryExt = Num::binaryExtend($asBinary,$defBits - strlen($asBinary), $asBinary[0]);
            }
            $j->add('as-binary', $binaryExt);
            $j->add('bytes', strlen($binaryExt)/8);
            $j->add('bits', strlen($binaryExt));
            $asHex = Num::binaryToHex($binaryExt);
            $j->add('as-hex', $asHex);
        }
        else{
            $j->add('as-hex', $asBinary);
        }
        $this->sendResponse('Finished.', FALSE, 200, '"response":'.$j);
    }
    
    private function binaryToHex(){
        $defBits = $this->defaultBytesCount*8;
        $binary = $this->getInputs()['binary'];
        $bytes = $this->getInputs()['bytes'];
        $j = new JsonX();
        $j->add('as-binary', '');
        $j->add('as-hex', '');
        $j->add('bits', 0);
        $j->add('bytes', 0);
        $len = strlen($binary);
        if($bytes*8 > $len){
            $binaryExt = Num::binaryExtend($binary,$bytes*8 - strlen($binary), $binary[0]);
        }
        else{
            $j->add('bytes', $defBits/8);
            $binaryExt = Num::binaryExtend($binary,$defBits - strlen($binary), $binary[0]);
        }
        $j->add('as-binary', $binaryExt);
        if($binaryExt != Num::INV_BINARY){
            $j->add('bytes', strlen($binaryExt)/8);
            $j->add('bits', strlen($binaryExt));
            $j->add('as-hex', Num::binaryToHex($binaryExt));
        }
        $this->sendResponse('Finished.', FALSE, 200, '"response":'.$j);
    }

    private function intToHex() {
        $j = new JsonX();
        $int = $this->getInputs()['integer'];
        $bytes = $this->getInputs()['bytes'];
        $defaultBitsCount = $this->defaultBytesCount*8;
        $j->add('as-integer', $int);
        $j->add('as-hex', '');
        $j->add('bits', 0);
        $j->add('bytes', 0);
        $hex = Num::intToHex($int);
        $asBinary = Num::hexToBinary($hex);
        if($hex != Num::INV_HEX){
            $len = strlen($asBinary);
            if($bytes*8 > $len){
                $j->add('bytes', $bytes);
                $asBinaryExt = Num::binaryExtend($asBinary, $bytes*8 - $len, $asBinary[0]);
            } 
            else{
                $asBinaryExt = Num::binaryExtend($asBinary, $defaultBitsCount - $len, $asBinary[0]);
                $j->add('bytes', $defaultBitsCount/8);
            }
            $j->add('bits', strlen($asBinaryExt));
        }
        $j->add('as-hex', Num::binaryToHex($asBinaryExt));
        $this->sendResponse('Finished.', FALSE, 200, '"response":'.$j);
    }
    
    private function intToBinary() {
        $j = new JsonX();
        $int = $this->getInputs()['integer'];
        $bytes = $this->getInputs()['bytes'];
        $defaultBitsCount = $this->defaultBytesCount*8;
        $j->add('as-integer', $int);
        $j->add('as-binary', '');
        $j->add('bits', 0);
        $j->add('bytes', 0);
        $binary = Num::intToBinary($int);
        if($binary != Num::INV_BINARY){
            $len = strlen($binary);
            if($bytes*8 > $len){
                $j->add('bytes', $bytes);
                $asBinaryExt = Num::binaryExtend($binary, $bytes*8 - $len, $binary[0]);
            } 
            else{
                $asBinaryExt = Num::binaryExtend($binary, $defaultBitsCount - $len, $binary[0]);
                $j->add('bytes', $defaultBitsCount/8);
            }
            $j->add('bits', strlen($asBinaryExt));
        }
        $j->add('as-binary', $asBinaryExt);
        $this->sendResponse('Finished.', FALSE, 200, '"response":'.$j);
    }
    
    public function processRequest() {
        $a = $this->getAction();
        if($a == 'int-to-binary'){
            $this->intToBinary();
        }
        else if($a == 'int-to-hex'){
            $this->intToHex();
        }
        else if($a == 'binary-to-int'){
            $this->actionNotImpl();
        }
        else if($a == 'binary-to-hex'){
            $this->binaryToHex();
        }
        else if($a == 'hex-to-binary'){
            $this->hexToBinary();
        }
        else if($a == 'hex-to-int'){
            $this->actionNotImpl();
        }
        else if($a == 'binary-add'){
            $this->actionNotImpl();
        }
        else if($a == 'binary-sub'){
            $this->actionNotImpl();
        }
        else if($a == 'hex-add'){
            $this->actionNotImpl();
        }
        else if($a == 'hex-sub'){
            $this->actionNotImpl();
        }
    }

}
$api = new NumsAPIs();
$api->process();
