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
    }

    // Check field listing
    public function testGet_Field() {
        $response = $this->client->get('/field');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), True);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(True, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals('Field', $data['Type']);

        $this->assertInternalType('array',$data['Data']);
        
    }

    // Check field addition
    public function testPost_NewField() {
        $response = $this->client->post('/field', [ 'json' => $this->field ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), True);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(True, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals('Field', $data['Type']);
        $this->assertArrayHasKey('Data', $data);
    }

    // Check field update
    public function testPut_Field() {
        $new_field = ['NAME' => 'Modified', 'REGEX' => '~^[0-9]{5}$~', 'DESCRIPTION' => 'Test field modified'];

        $response = $this->client->put('/field/test', [ 'json' => $new_field ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), True);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(True, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals('Field', $data['Type']);
        $this->assertArrayHasKey('Data', $data);
        
        $this->assertEquals($new_field['NAME'], $data['Data']['NAME']);
        $this->assertEquals($new_field['DESCRIPTION'], $data['Data']['DESCRIPTION']);
        $this->assertEquals($new_field['REGEX'], $data['Data']['REGEX']);
    }

    // Check that a field can be registered with a named regex
    public function testPut_NamedField() {
        $new_field = ['NAME' => 'Duplicated', 'REGEX' => 'modified', 'DESCRIPTION' => 'Test field duplicated'];

        $response = $this->client->post('/field', [ 'json' => $new_field ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), True);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(True, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals('Field', $data['Type']);
        $this->assertArrayHasKey('Data', $data);
        
        $this->assertEquals($new_field['NAME'], $data['Data']['NAME']);

        // Check that field duplicated exists
        $response = $this->client->get('/field/duplicated');
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), True);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(True, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals('Field', $data['Type']);
        $this->assertArrayHasKey('Data', $data);
        $this->assertEquals($new_field['DESCRIPTION'], $data['Data']['Duplicated']['DESCRIPTION']);
        $this->assertEquals('~^[0-9]{5}$~', $data['Data']['Duplicated']['REGEX']);
    }

    // Check field deletion
    public function testDelete_Field() {
        $response = $this->client->delete('/field/modified');
        $this->assertEquals(200, $response->getStatusCode());
        
        $response = $this->client->delete('/field/duplicated');
        $this->assertEquals(200, $response->getStatusCode());

        // Check that field tested no longer exists
        $response = $this->client->get('/field');
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), True);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(True, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals('Field', $data['Type']);
        
        $this->assertArrayNotHasKey('Test', $data['Data']);
        $this->assertArrayNotHasKey('Modified', $data['Data']);
        $this->assertArrayNotHasKey('Duplicated', $data['Data']);
    }
}
?>
