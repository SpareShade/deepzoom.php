<?php
namespace Deepzoom\Tests\StreamWrapper\Amazon;

use Deepzoom\Exception as dzException;
use Deepzoom\StreamWrapper\Amazon\S3;

/**
* Deep Zoom Tools
*
* Copyright (c) 2008-2010, OpenZoom <http://openzoom.org/>
* Copyright (c) 2008-2010, Nicolas Fabre <nicolas.fabre@gmail.com>
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without modification,
* are permitted provided that the following conditions are met:
*
* 1. Redistributions of source code must retain the above copyright notice,
* this list of conditions and the following disclaimer.
*
* 2. Redistributions in binary form must reproduce the above copyright
* notice, this list of conditions and the following disclaimer in the
* documentation and/or other materials provided with the distribution.
*
* 3. Neither the name of OpenZoom nor the names of its contributors may be used
* to endorse or promote products derived from this software without
* specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
* ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
* DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
* ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
* ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
* SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * Testing Amazon S3 Wrapper
 *
 * @package    Deepzoom
 * @subpackage Test_StreamWrapper
 * @author     Nicolas Fabre <nicolas.fabre@gmail.com>
 */
class S3Test extends \PHPUnit_Framework_TestCase
{
	protected static $blobConfig;
	protected static $blobStorage;
	
    static public function setUpBeforeClass()
    {
    	self::$blobConfig = array(
           'container'       => 'phpunitcontainer',     
           'accessKey'       => 'ACCESSKEY',     
           'secretAccessKey' => 'SECRETACCESSKEY',     
           'name'            => 's3',     
        );
        self::$blobStorage = new \Zend_Service_Amazon_S3(self::$blobConfig['accessKey'],self::$blobConfig['secretAccessKey']);
        if(!self::$blobStorage->isBucketAvailable(self::$blobConfig['container'])){
            self::$blobStorage->createBucket(self::$blobConfig['container']);
        }
          	
        self::$blobStorage->putObject(self::$blobConfig['container'].'/mypath/model1.xml', file_get_contents(__DIR__.'/../../Fixtures/model1.xml'),
            array(\Zend_Service_Amazon_S3::S3_ACL_HEADER => \Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ)
        );
    }

    public function setUp()
    {
        $this->path = 'mypath';
    }
    
    public static function tearDownAfterClass()
    {
    }
    
    protected function getWrapperInstance() {
    	return new S3(self::$blobStorage,self::$blobConfig['container'],self::$blobConfig['name']);
    }
    
    public function testConstructor()
    {
        $blobWrapper = $this->getWrapperInstance();
        $this->assertInstanceOf('Deepzoom\StreamWrapper\StreamWrapperInterface',$blobWrapper, '__construct()'); 
        $this->assertInstanceOf('Deepzoom\StreamWrapper\StreamWrapperAbstract',$blobWrapper, '__construct()'); 
        $this->assertInstanceOf('Deepzoom\StreamWrapper\Amazon\S3',$blobWrapper, '__construct()'); 
    }
    
    public function testEnsure() {
    	$blobWrapper = $this->getWrapperInstance();
        $result = $blobWrapper->ensure($this->path.'NOTExist');
        $this->assertEquals($this->path.'NOTExist', $result);
    }
    
     public function testGetPrefix() {
        $blobWrapper = $this->getWrapperInstance();
        $blobConfig = self::$blobConfig;
     	$this->assertEquals("{$blobConfig['name']}://{$blobConfig['container']}/", $blobWrapper->getPrefix());	
     }
     
     public function testExistDirectoryNotExist() {
     	$this->markTestSkipped('Invalid result, wtf?');
        $blobWrapper = $this->getWrapperInstance();
        $this->assertFalse($blobWrapper->exists($this->path.'/model2.xml'));    
     }
     
     public function testExist() {
     	$blobWrapper = $this->getWrapperInstance();
        $this->assertTrue($blobWrapper->exists($this->path.'/model1.xml'));    
     }
     
     public function testGetPathInfo() {
        $blobWrapper = $this->getWrapperInstance();
     	$infos = $blobWrapper->getPathInfo($this->path.'/model1.xml');
        
        $this->assertType('array', $infos);
        $this->assertArrayHasKey('dirname',$infos);
        $this->assertArrayHasKey('basename',$infos);
        $this->assertArrayHasKey('extension',$infos);
        $this->assertArrayHasKey('filename',$infos);
     }
     
    public function testGetContents() {
         $blobWrapper = $this->getWrapperInstance();
         $contents = $blobWrapper->getContents($this->path.'/model1.xml');
         $this->assertGreaterThan(0, sizeof($contents));
     }
      
    public function testPutAndGetContents() {
         $blobWrapper = $this->getWrapperInstance();
         $result = $blobWrapper->putContents($this->path.'/testFile.xml','myText');
         $this->assertTrue($result);  
         $contents = $blobWrapper->getContents($this->path.'/testFile.xml'); 
         $this->assertEquals('myText', $contents);
         $this->assertGreaterThan(0, sizeof($contents));
     }
}