<?php

namespace Parables\JsonHyperSchema;

use Psr\Http\Message\ResponseInterface;

class HyperResource
{
    private function __construct(
        private array $instance,
        private ?HyperSchema $schema = null,
        private ?HyperClient $client = null,
    ) {
        $this->client = $client ?? new HyperClient();
    }

    public static function fromResponse(ResponseInterface $response, HyperClient $client): self
    {
        $schema = HyperSchema::fromHeaders(
            headers: $response->getHeaders(),
            client: $client
        );

        $instance = json_decode(json: $response->getBody(), associative: true);

        return new self(
            instance: $instance,
            schema: $schema,
            client: $client,
        );
    }

    public static function bind(
        array $instance,
        string $url,
        ?HyperClient $client = null,
    ): self {
        $schema = HyperSchema::lazy(url: $url, client: $client);

        return new self(
            instance: $instance,
            schema: $schema,
            client: $client,
        );
    }

    /** returns the hyper-schema which can be accessed like an array  */
    public function schema() : ?HyperSchema {
        return $this->schema;
    }

    public function links() /*: HyperLinks*/  {
        // if(!isset($this->schema))
        // {
            // return HyperLinks::empty();
        // }

        // return $this->schema->links();
        return  $this->schema['links'];
    }

    //
    //
    // public function rel($name, array $option = [])
    // {
    //     $link = $this->schema->link(rel: $name);
    //     $method = $link->method(default: 'GET');
    //     $href = $link->href();
    //     $params = $option['params'] ?? [];
    //
    //     // TODO: filter out unused params and append them as query strings
    //
    //     $url = $link->getUrl(params: $params);
    //     $options = [];
    //
    //     return $this->client->resource(url: $url, options: $options);
    // }
}
