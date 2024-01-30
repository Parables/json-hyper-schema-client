<?php

namespace Parables\JsonHyperSchema;

use ArrayAccess;
use LogicException;

/** @implements ArrayAccess<string, mixed> */
class HyperSchema implements ArrayAccess
{

    private array $schema;
    private string $url;
    private ?HyperClient $client;

    private function __construct(
    ) {
        $this->client ??= new HyperClient();
    }

    private function withUrl(string $url): self{
        $this->url = $url;
        return $this;
    }

    private function withSchema(array $schema): self{
        $this->schema = $schema;
        return $this;
    }


    private function withClient(?HyperClient $client = null): self{
        $this->client = $client ?? new HyperClient();
        return $this;
    }

    public static function create(
        string $url,
        array $schema,
        HyperClient $client = null
    ) {
        return (new self)
            ->withUrl(url: $url)
            ->withSchema(schema: $schema)
            ->withClient(client: $client);
    }

    public static function lazy(
        string $url,
        ?HyperClient $client = null
    ) {
        return (new self)
            ->withUrl(url: $url)
            ->withClient(client: $client);
    }

    public static function fromHeaders(
        array $headers,
        HyperClient $client = null
    ): ?self {
        $url = self::extractSchemaUrlFromHeaders(headers: $headers);

        if(! empty($url)){
            return $client->schema(url: $url);
        }

        return null; 
    }

    public static function extractSchemaUrlFromHeaders(
        array $headers = [],
    ): ?string {
        $headers = array_keys_transform(payload: $headers, callback: 'kebab_case');
        if ($contentType = ($headers['content-type'] ?? [])) {
            preg_match(
                pattern: '/profile=[\'"]*(?<url>[^\';"\s;]+)/',
                subject: implode(', ', array_wrap($contentType)),
                matches: $matches
            );
            $extracted = $matches['url'] ?? $matches[1] ?? null;

            return empty($extracted) ? null : $extracted;
        }

        return null;
    }

    private function schema(): array
    {
        if (isset($this->schema)) {
            return $this->schema;
        }

        return $this->loadSchema()->schema();
    }

    private function loadSchema(): self
    {
        return $this->client->schema(url: $this->url);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->schema()[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->schema()[$offset]??null;
    }

    public function offsetSet($offset, $value): void
    {
        // $this->schema()[$offset] = $value;
        throw new LogicException("HyperSchema is immutable and cannot be set");
    }

    public function offsetUnset($offset): void
    {
        // unset($this->schema()[$offset]);
        throw new LogicException("HyperSchema is immutable and cannot be unset");
    }

    // public function __get($name)
    // {
    //     return $this->schema()[$name] ?? null;
    // }
    //
    // public function __set($name, $value): void
    // {
    //     throw new LogicException("HyperSchema is immutable and cannot be set");
    // }
    //
    // public function __isset($name)
    // {
    //     return isset($this->schema()[$name]);
    // }
    //
    // public function __unset($name): void
    // {
    //     throw new LogicException("HyperSchema is immutable and cannot be unset");
    // }
}
