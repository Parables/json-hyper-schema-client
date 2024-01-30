<?php

namespace Parables\JsonHyperSchema;

class InMemorySchemaRepository implements SchemaRepository
{
    private static ?InMemorySchemaRepository $instance;

    protected $cache = [];

    private function __construct()
    {
    }

    public static function create(): static {
        return new self();
    }

    public static function getInstance(bool $reset = false): static
    {
        if ($reset) {
            self::$instance = null;  // Clear the existing instance
        }

        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get(string $url): ?array
    {
        return $this->cache[$url] ?? null;
    }

    public function set(string $url, array $schema): self
    {
        $this->cache[$url] = $schema;

        return $this;
    }
}
