<?php

/**
* Elixir, Stored Objects management
* @author Benoit Malchrowicz
* @version 1.0
*
* Copyright © 2014-2016 Benoit Malchrowicz
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or any later
* version.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
* Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*
*/

use PHPUnit\Framework\TestCase;

require('vendor/autoload.php');
define('__ROOT_URL__','http://elixir.lxr');

class FieldTest extends TestCase
{
    protected $client;
    protected $field;

    // Instantiate bot and field example object
    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([ 'base_uri' => __ROOT_URL__ ]);
        $this->field = ['NAME' => 'test', 'REGEX' => '~^[0-9]{3}$~', 'DESCRIPTION' => 'Test field'];
        $this->type = 'Field';
    }

    private function valid_request($method, $uri, $params=NULL, $hasResult=False){
        if(!empty($params)){
            $response = $this->client->$method($uri, [ 'json' => $params ]);
        }else{
            $response = $this->client->$method($uri);
        }
        
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), True);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(True, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals($this->type, $data['Type']);

        if($hasResult){
            $this->assertArrayHasKey('Data', $data);
            $this->assertInternalType('array',$data['Data']);
        }
            
        return $data;
    }

    private function invalid_request($method, $uri, $params=NULL){
        if(!empty($params)){
            $response = $this->client->$method($uri, [ 'json' => $params ]);
        }else{
            $response = $this->client->$method($uri);
        }
        
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), True);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(False, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals($this->type, $data['Type']);
        $this->assertArrayHasKey('Code', $data);

        return $data;
    }

    // Check field listing
    public function testGet_Field() {
        $this->valid_request('get','/field',NULL,TRUE);
    }

    /**
     * @depends testGet_Field
     */
    // Check field addition
    public function testPost_Field() {
        $this->valid_request('post','/field', $this->field,TRUE);
    }

    /**
     * @depends testPost_Field
     */
    // Check field value is valid
    public function testGet_ValidField() {
        $this->valid_request('get','/field/test/123');
    }

    /**
     * @depends testPost_Field
     */
    // Check field value is invalid
    public function testGet_InvalidField() {
        $this->invalid_request('get','/field/test/abc');
    }

    /**
     * @depends testPost_Field
     */
    // Check field update
    public function testPut_Field() {
        $new_field = ['NAME' => 'Modified', 'REGEX' => '~^[0-9]{5}$~', 'DESCRIPTION' => 'Test field modified'];
        $data = $this->valid_request('put','/field/test', $new_field, TRUE);
        $this->assertEquals($new_field['NAME'], $data['Data']['NAME']);
        $this->assertEquals($new_field['DESCRIPTION'], $data['Data']['DESCRIPTION']);
        $this->assertEquals($new_field['REGEX'], $data['Data']['REGEX']);
    }

    // Check that a field can be registered with a named regex
    /**
     * @depends testPost_Field
     */
    public function testPost_NamedField() {
        $new_field = ['NAME' => 'Duplicated', 'REGEX' => 'modified', 'DESCRIPTION' => 'Test field duplicated'];
        $data = $this->valid_request('post','/field', $new_field, TRUE);
        $this->assertEquals($new_field['NAME'], $data['Data']['NAME']);

        // Check that field duplicated exists
        $data = $this->valid_request('get','/field/duplicated',NULL,TRUE);
        $this->assertEquals($new_field['DESCRIPTION'], $data['Data']['Duplicated']['DESCRIPTION']);
        $this->assertEquals('~^[0-9]{5}$~', $data['Data']['Duplicated']['REGEX']);
    }

    // Check field deletion
    /**
     * @depends testPut_Field
     */
    public function testDelete_Field() {
        $this->valid_request('delete','/field/modified');
        $this->valid_request('delete','/field/duplicated');
        
        // Check that field tested no longer exists
        $data = $this->valid_request('get','/field',NULL,TRUE);
        $this->assertArrayNotHasKey('Test', $data['Data']);
        $this->assertArrayNotHasKey('Modified', $data['Data']);
        $this->assertArrayNotHasKey('Duplicated', $data['Data']);
    }
}
?>
