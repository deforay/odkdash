<?php

declare(strict_types=1);

namespace App\Http;

use ArrayObject;

/**
 * Request values as the services expect them:
 *
 * - $params['missing'] is null, without a warning.
 * - $params->name reads and writes like $params['name'].
 * - get($name, $default) returns $default for a missing key. set($name, $value) writes one.
 *
 * @extends ArrayObject<array-key, mixed>
 */
final class Parameters extends ArrayObject
{
    /** @param array<array-key, mixed> $values */
    public function __construct(array $values = [])
    {
        parent::__construct($values, ArrayObject::ARRAY_AS_PROPS);
    }

    public function offsetGet(mixed $key): mixed
    {
        return $this->offsetExists($key) ? parent::offsetGet($key) : null;
    }

    public function get(string|int $name, mixed $default = null): mixed
    {
        return $this->offsetExists($name) ? parent::offsetGet($name) : $default;
    }

    public function set(string|int $name, mixed $value): self
    {
        $this[$name] = $value;
        return $this;
    }

    /** @return array<array-key, mixed> */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }
}
