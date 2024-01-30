<?php

namespace Parables\JsonHyperSchema;

/*
it('can make a HTTP request', function () {

    $response = HyperClient::request(url: 'http://localhost');

    expect(true)->toBeTrue();
});
*/

it('returns null if it cant extract the schema url given a response header', function ($headers) {

    $extractedUrl = HyperSchema::extractSchemaUrlFromHeaders($headers);

    expect($extractedUrl)->tobe(null);
})->with([
    [['Content-Type' => 'application/json; charset=utf-8;']],
    [['Content-Type' => 'application/json; charset=utf-8; profile=']],
    [['Accept' => 'application/json']],
    [['contentType' => 'application/json; charset=utf-8; profile=""']],
    [['content-type' => 'application/json charset=utf-8']],
]);

it('can extract the schema url given a response header', function ($contentType) {

    $schemaUrl = 'http://localhost/api/schema/person';
    $headers = [
        'Content-Type' => "$contentType",
    ];

    $extractedUrl = HyperSchema::extractSchemaUrlFromHeaders($headers);

    expect($extractedUrl)->tobe($schemaUrl);
})->with([
    'application/json; charset=utf-8; profile=http://localhost/api/schema/person',
    'profile=http://localhost/api/schema/person; application/json; charset=utf-8;',
    'application/json; charset=utf-8; profile="http://localhost/api/schema/person"',
    "application/json; charset=utf-8; profile='http://localhost/api/schema/person'",
    "application/json; profile='http://localhost/api/schema/person'; charset=utf-8; ",
    "application/json; profile='http://localhost/api/schema/person' charset=utf-8;",
    'application/json; profile=http://localhost/api/schema/person charset=utf-8;',
    'application/json profile=http://localhost/api/schema/person charset=utf-8',

]);

/*
it('can access the schema as array key', function () {

    $schemaUrl = 'http://localhost/api/schema/person';
    $schema = Person::jsonSchema();
    $client = new HyperClient();

    $hyperSchema = HyperSchema::create(client: $client, url: $schemaUrl, rawSchema: $schema);

    expect($hyperSchema['id'])->toBe($schemaUrl);
});

it('can load the schema given a response header', function () {

})->todo();
*/
