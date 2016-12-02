<?php

require('vendor/autoload.php');
define('__ROOT_URL__','http://elixir.lxr');

class FieldTest extends PHPUnit_Framework_TestCase
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
    public function testGet_Field() {
        $data = $this->valid_request('get','/field');
    }

    // Check field addition
    public function testPost_NewField() {
        $data = $this->valid_request('post','/field', $this->field);
    }

    // Check field update
    public function testPut_Field() {
        $new_field = ['NAME' => 'Modified', 'REGEX' => '~^[0-9]{5}$~', 'DESCRIPTION' => 'Test field modified'];
        $data = $this->valid_request('put','/field/test', $new_field);
        $this->assertEquals($new_field['NAME'], $data['Data']['NAME']);
        $this->assertEquals($new_field['DESCRIPTION'], $data['Data']['DESCRIPTION']);
        $this->assertEquals($new_field['REGEX'], $data['Data']['REGEX']);
    }

    // Check that a field can be registered with a named regex
    public function testPost_NamedField() {
        $new_field = ['NAME' => 'Duplicated', 'REGEX' => 'modified', 'DESCRIPTION' => 'Test field duplicated'];
        $data = $this->valid_request('post','/field', $new_field);
        $this->assertEquals($new_field['NAME'], $data['Data']['NAME']);

        // Check that field duplicated exists
        $data = $this->valid_request('get','/field/duplicated');
        $this->assertEquals($new_field['DESCRIPTION'], $data['Data']['Duplicated']['DESCRIPTION']);
        $this->assertEquals('~^[0-9]{5}$~', $data['Data']['Duplicated']['REGEX']);
    }

    // Check field deletion
    public function testDelete_Field() {
        $data1 = $this->valid_request('delete','/field/modified');
        $data1 = $this->valid_request('delete','/field/duplicated');
        
        // Check that field tested no longer exists
        $data = $this->valid_request('get','/field');
        $this->assertArrayNotHasKey('Test', $data['Data']);
        $this->assertArrayNotHasKey('Modified', $data['Data']);
        $this->assertArrayNotHasKey('Duplicated', $data['Data']);
    }
}
?>
