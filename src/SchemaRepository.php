<?php

namespace Parables\JsonHyperSchema;

interface SchemaRepository
{
    public function get(string $url): ?array;

    public function set(string $url, array $schema): self;
}
