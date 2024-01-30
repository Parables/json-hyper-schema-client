<?php

namespace Parables\JsonHyperSchema;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use Mockery;

dataset(
    name: 'jsonHyperSchemas',
    dataset: [
        'apiSchema' => [[
            '$id' => 'http://test.localhost/docs/schema/api',
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'base' => 'http://test.localhost/api',
            'title' => 'API Endpoint',
            'description' => 'This is the entry point for the API',
            'links' => [
                [
                    'title' => 'Commands',
                    'description' => 'returns a list of commands you can execute',
                    'rel' => 'commands',
                    'href' => '/commands',
                    'headerSchema' => [
                        '$ref' => '/#/$defs/headerSchema',
                    ],
                ],
                [
                    'title' => 'Queries',
                    'description' => 'returns a list of queries you can send',
                    'rel' => 'queries',
                    'href' => '/queries',
                    'headerSchema' => [
                        '$ref' => '/#/$defs/headerSchema',
                    ],
                ],
            ],
            '$defs' => [
                'headerSchema' => [
                    'type' => 'object',
                    'required' => [
                        'Authorization',
                        'Accept',
                    ],
                    'properties' => [
                        'Authorization' => [
                            'type' => 'string',
                            'pattern' => '/^Bearer .+/',
                        ],
                        'Accept' => [
                            'type' => 'string',
                            'const' => 'application/json',
                        ],
                    ],
                ],
            ],
        ],]
    ],
);

it('can lazily create a HyperSchema', function(){
    $url = 'http://test.localhost/docs/schema/api';
 $schema = HyperSchema::lazy(url:$url );
expect($schema)->toBeInstanceOf(HyperSchema::class);
});

it('can bind an instance to a schema', function(){

     $instance = [];
    $url = 'http://test.localhost/docs/schema/api'; 
    $client = new HyperClient();

    $resource = HyperResource::bind(
        instance: $instance, 
        url: $url, 
        client:  $client
    );

    expect($resource)->toBeInstanceOf(HyperResource::class);
});

it('will create a HyperClient instance without any arguments', function(){

    $client = new HyperClient();
expect($client)->toBeInstanceOf(HyperClient::class);
});

it('allows you to pass in a custom GuzzleClient', function(){

    $guzzleClient = Mockery::mock(new GuzzleClient(config: [
// NOTE: you can customize GuzzleClient and pass it to the HyperClient
// See: https://docs.guzzlephp.org/en/stable/request-options.html
// You can use this to pre-authenticate the GuzzleClient and pass it to the HyperClient 
 'headers' => [
'authToken' => 'some-api-token'
],
]));
    $client = new HyperClient(client: $guzzleClient);
expect($client)->toBeInstanceOf(HyperClient::class);
});


it('will not load or fetch the schema when binding an instance with a schema', function(){
    $guzzleClient = Mockery::spy(GuzzleClient::class);
  $schemaRepository =  Mockery::spy(InMemorySchemaRepository::class);

    $instance = [];
    $url = 'http://test.localhost/docs/schema/api'; 
    $client = new HyperClient(client: $guzzleClient, repository: $schemaRepository);

   $resource = HyperResource::bind(
        instance: $instance, 
        url: $url, 
        client:  $client
    );

    expect($resource)->toBeInstanceOf(HyperResource::class);

    $schemaRepository->shouldNotHaveReceived('get');
$guzzleClient->shouldNotHaveReceived('request');

});

it('will only fetch the resource', function () {
    $guzzleClient = Mockery::mock(GuzzleClient::class);
    $guzzleClient->shouldReceive('request')
        ->with(
            'GET',
            'http://localhost/api/',
            Mockery::any()
        )
        ->andReturn(
new Response(
            status: 200,
            headers: [
                'Location' => 'http://localhost/api/',
                // NOTE: it will not fetch the schema because the `profile` value 
                // is not set in the response Content-Type header
                    'Content-Type' => 'application/json;',
            ],
            body: json_encode([])
        )
)->once();

    $hyperClient = new HyperClient(client: $guzzleClient);
    $hyperClient->resource( url: 'http://localhost/api/');

});

it('will fetch both the resource and the schema', function ($apiSchema) {
    $guzzleClient = Mockery::mock(GuzzleClient::class);
    $guzzleClient->shouldReceive('request')
        ->andReturn(
            new Response(
                status: 200,
                headers: [
                    'Location' => 'http://test.localhost/api/',
                // NOTE: it will fetch the schema because the `profile` value 
                // is set in the response Content-Type header
                    'Content-Type' => 'application/json; profile="http://test.localhost/docs/schema/api"',
                ],
                body: json_encode([]),
            ),
            new Response(
                status: 200,
                headers: [
                    'Location' => 'http://test.localhost/docs/schema/api',
                ],
                body: json_encode($apiSchema),
            ),
        )->twice();

    $hyperClient = new HyperClient(client: $guzzleClient);
  $resource =  $hyperClient->resource(
        url: 'http://test.localhost/api/',
        ); 
})->with('jsonHyperSchemas');

it('will load the schema from the SchemaRepository once it has been fetched', function($apiSchema){

    $url = 'http://test.localhost/docs/schema/api';
    $guzzleClient = Mockery::spy(GuzzleClient::class);
    $schemaRepository =  Mockery::spy(InMemorySchemaRepository::getInstance());
$client = Mockery::spy(
new HyperClient(
client: $guzzleClient, 
repository: $schemaRepository
 )
);

    expect($schemaRepository->get(url: $url))->toBe($apiSchema);

    $hyperClient = new HyperClient(client: $guzzleClient, repository: $schemaRepository);
    $schema = $hyperClient->schema(url: 'http://test.localhost/docs/schema/api');
})
->with('jsonHyperSchemas')
->depends('it will fetch both the resource and the schema');

it('can access the schema as an array', function($apiSchema){

     $instance = [];
    $url = 'http://test.localhost/docs/schema/api'; 
  $resource =  HyperResource::bind(
        instance: $instance, 
        url: $url, 
    );

    $schema = $resource->schema();
    expect($schema)->toBeInstanceOf(HyperSchema::class);

$links = $schema['links']?? 'there is nothing here';
expect($links)->toBe($apiSchema['links']);

})
->with('jsonHyperSchemas')
->depends('it will fetch both the resource and the schema');


it('will load or fetch a lazily created schema only if is accessed', function($apiSchema){

    $url = 'http://test.localhost/docs/schema/api'; 

    $guzzleClient = Mockery::spy(GuzzleClient::class);
    $repository =  Mockery::spy(InMemorySchemaRepository::getInstance());
$client = Mockery::spy(
new HyperClient(
client: $guzzleClient, 
repository: $repository
 )
);
    $schema = Mockery::spy(HyperSchema::lazy(url: $url, client: $client));

$links = $schema['links']?? 'there is nothing here';
expect($links)->toBe($apiSchema['links']);

$schema->shouldHaveReceived('offsetExists');
// $schema->shouldHaveReceived('schema'); // NOTE: this is private so it can't be mocked 
// $schema->shouldHaveReceived('loadSchema'); // NOTE: this is private so it can't be mocked
$schema->shouldHaveReceived('offsetGet');
// $schema->shouldHaveReceived('schema'); // NOTE: this is private so it can't be mocked 
// $schema->shouldHaveReceived('loadSchema'); // NOTE: this is private so it can't be mocked
$client->shouldHaveReceived('schema');
    $repository->shouldHaveReceived('get');
$guzzleClient->shouldNotHaveReceived('request');



    $guzzleClient = Mockery::spy(GuzzleClient::class);
    $guzzleClient->shouldReceive('request')
        ->andReturn(
            new Response(
                status: 200,
                headers: [
                    'Location' => 'http://test.localhost/docs/schema/api',
                ],
                body: json_encode($apiSchema),
            ),
        )->once();
// NOTE: InMemorySchemaRepository::create() creates a new instance
// which doesn't have the previously cached schema.
// This will make the HyperClient request the schema using the url
    $repository =  Mockery::spy(InMemorySchemaRepository::create());
$client = Mockery::spy(
new HyperClient(
client: $guzzleClient, 
repository: $repository
 )
);
    $schema = Mockery::spy(HyperSchema::lazy(url: $url, client: $client));

$links = $schema['links']?? 'there is nothing here';
expect($links)->toBe($apiSchema['links']);

$schema->shouldHaveReceived('offsetExists');
// $schema->shouldHaveReceived('schema'); // NOTE: this is private so it can't be mocked
// $schema->shouldHaveReceived('loadSchema'); // NOTE: this is private so it can't be mocked
$schema->shouldHaveReceived('offsetGet');
// $schema->shouldHaveReceived('schema'); // NOTE: this is private so it can't be mocked 
// $schema->shouldHaveReceived('loadSchema'); // NOTE: this is private so it can't be mocked
$client->shouldHaveReceived('schema');
    $repository->shouldHaveReceived('get');
$guzzleClient->shouldHaveReceived('request');

})
->with('jsonHyperSchemas')
->depends('it will fetch both the resource and the schema');

