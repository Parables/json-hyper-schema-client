<?php

namespace Parables\JsonHyperSchema;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class HyperClient
{
    public function __construct(
        private ?GuzzleClient $client = null, private ?SchemaRepository $repository = null
    ) {
        $this->client = $client ?? new GuzzleClient();
        $this->repository = $repository ?? InMemorySchemaRepository::getInstance();
    }

    public function resource(string $url, mixed ...$options): HyperResource
    {
        $response = $this->request($url, $options);

        return HyperResource::fromResponse(response: $response, client: $this);
    }

    public function schema($url, $options = []): HyperSchema
    {
        $schema = $this->repository->get(url: $url);

        if(empty($schema)){
        $data = $this->request(url: $url, options: $options)->getBody();
        $schema = json_decode($data, true);
            $this->repository->set(url: $url, schema: $schema);
        }

        return HyperSchema::create(
            url: $url,
            schema: $schema,
            client: $this,
        );
    }

    private function request(string $url, array $options = []): ResponseInterface
    {
        $options['headers'] ??= [];
        $options['headers']['content-type'] ??= 'application/json';

        $options['method'] = strtoupper($options['method'] ?? 'GET');

        $response = $this->client->request(
            method: $options['method'],
            uri: $url,
            options: $options
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            throw new \Exception("HTTP Error: $statusCode => {$response->getReasonPhrase()}");
        }

        return $response;
    }
}
