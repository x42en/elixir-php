<?php

require('vendor/autoload.php');

// Run the tests:
//# php vendor/bin/phpunit FieldTest.php

// Process:
// List objects (should return an empty list)
// POST field -> True
// GET field -> check object
// PUT field -> check object
// DELETE field -> ok

// Try to get unknown object -> get error
// Try to set invalid field -> get error
// Try to set invalid regex -> get error
// Try to set invalid description -> no error (desc empty)
// Try to modify unexisting field -> get error
// Try to modify system field -> get error
// Try to update invalid field -> get error
// Try to update invalid regex -> get error
// Try to update invalid description -> no error (same desc)

define('__ROOT_URL__','http://elixir.lxr');

class FieldTest extends PHPUnit_Framework_TestCase
{
    protected $client;
    protected $field;

    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
                    'base_uri' => __ROOT_URL__
                    ]);
        $this->field = ['Name' => 'test', 'Regex' => '~^[0-9]{3}$~', 'Description' => 'Test field'];
    }

    public function testGet_Field() {
        echo "[+] List field\n";

        $response = $this->client->get('/field');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(True, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals('Field', $data['Type']);
        
        $this->assertArrayHasKey('Name', $data['Data']);
        $this->assertArrayHasKey('REGEX', $data['Data']['Name']);
        $this->assertArrayHasKey('DESCRIPTION', $data['Data']['Name']);
        
    }

    public function testPost_NewField() {
        // $bookId = uniqid();
        echo "[+] Create field\n";

        $response = $this->client->post('/field', [ 'json' => $this->field ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(True, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals('Field', $data['Type']);
        
        $this->assertArrayHasKey('ID', $data['Data']);
        $this->fieldID = $data['Data']['ID'];
    }

    public function testPut_Field() {
        echo "[+] Update field\n";

        $new_field = ['Name' => 'modified', 'Regex' => '~^[0-9]{5}$~', 'Description' => 'Test field modified'];

        $response = $this->client->put('/field/Test', [ 'json' => $new_field ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        
        $this->assertArrayHasKey('State', $data);
        $this->assertEquals(True, $data['State']);
        $this->assertArrayHasKey('Type', $data);
        $this->assertEquals('Field', $data['Type']);
        
        // should return the modified field...
        echo $response->getBody();
        // $this->assertEquals($new_field, $data['Data']);
    }

    public function testDelete_Field() {
        echo "[+] Delete field\n";

        $response = $this->client->delete('/field/Modified', [ 'http_errors' => false ]);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
?>
