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

class StructTest extends TestCase
{
    protected $client;
    protected $structure;

    // Instantiate bot and Structure example object
    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([ 'base_uri' => __ROOT_URL__ ]);
        $struct = json_encode(array('albums_id'=> array("type"=>'field','required'=>False,'default'=>null)));
        $this->structure = ['NAME'=>'TEST','STRUCT' => $struct, 'DESCRIPTION' => 'Test structure'];
        $this->type = 'Struct';
    }

    private function valid_request($method, $uri, $params=NULL, $hasResult=FALSE){
        if(!empty($params)) $response = $this->client->$method($uri, [ 'json' => $params ]);
        else $response = $this->client->$method($uri);
        
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), TRUE);
        if($method == 'post') echo $response->getBody();
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(TRUE, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals($this->type, $data['Type']);

        if($hasResult){
            $this->assertArrayHasKey('Data', $data);
            $this->assertInternalType('array',$data['Data']);
        }
        
        return $data;
    }

    // Check Structure listing
    public function testGet_Structure() {
        $this->valid_request('get','/struct',NULL,TRUE);
    }

    // Check Structure addition
    /**
     * @depends testGet_Structure
     */
    public function testPost_Structure() {
        $this->valid_request('post','/struct', $this->structure, TRUE);
    }

    // Check Structure update
    /**
     * @depends testPost_Structure
     */
    public function testPut_Structure() {
        $struct = json_encode(array('photos_id'=> array("type"=>'field','required'=>False,'default'=>null)));
        $new_structure = ['NAME' => 'Modified','STRUCT' => $struct, 'DESCRIPTION' => 'Modified Test structure'];
        
        $data = $this->valid_request('put','/struct/test', $new_struct, TRUE);

        $this->assertEquals($new_structure['MODIFIED'], $data['Data']['MODIFIED']);
    }

    // Check Structure deletion
    /**
     * @depends testPut_Structure
     */
    public function testDelete_Structure() {
        $this->valid_request('delete','/struct/test');
        
        // Check that field tested no longer exists
        $data = $this->valid_request('get','/struct', NULL, TRUE);
        $this->assertArrayNotHasKey('4242', $data['Data']);
    }
}
?>
