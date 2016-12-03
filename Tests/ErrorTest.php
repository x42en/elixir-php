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

class ErrorTest extends TestCase
{
    protected $client;
    protected $error;

    // Instantiate bot and field example object
    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([ 'base_uri' => __ROOT_URL__ ]);
        $this->error = ['CODE' => 4242, 'MESSAGE' => 'The ultimate error', 'LANG' => 'en'];
        $this->type = 'Error';
    }

    private function valid_request($method, $uri, $params=NULL){
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

        if($method != 'delete'){
            $this->assertArrayHasKey('Data', $data);
            $this->assertInternalType('array',$data['Data']);
        }
            
        return $data;
    }

    // Check field listing
    public function testGet_Error() {
        $this->valid_request('get','/error');
    }

    // Check field addition
    public function testPost_Error() {
        $this->valid_request('post','/error', $this->error);
    }

    // Check Error update
    /**
     * @depends testPost_Error
     */
    public function testPut_Error() {
        $new_error = ['CODE' => 4242, 'MESSAGE' => 'THE Error', 'LANG' => 'en'];
        $data = $this->valid_request('put','/error/4242', $new_error);

        $this->assertEquals($new_error['CODE'], $data['Data']['CODE']);
        $this->assertEquals($new_error['MESSAGE'], $data['Data']['MESSAGE']);
        $this->assertEquals($new_error['LANG'], $data['Data']['LANG']);
    }

    // Check Error deletion
    /**
     * @depends testPut_Error
     */
    public function testDelete_Error() {
        $this->valid_request('delete','/error/4242');
        
        // Check that field tested no longer exists
        $data = $this->valid_request('get','/error');
        $this->assertArrayNotHasKey('4242', $data['Data']);
    }
}
?>
