<?php

namespace Parables\JsonHyperSchema;

dataset(
    name: 'apiSchema',
    dataset: [[
        'apiSchemas' => [
            // '$id' => 'http://test.localhost/docs/schema/api',
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
        ],
    ],],
);
