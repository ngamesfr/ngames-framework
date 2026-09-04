<?php

declare(strict_types=1);

namespace Ngames\Framework;

/**
 * Typed accessors over a request: JSON body fields, query parameters and uploaded files.
 * A missing or mistyped value yields the default instead of a notice or a loose cast.
 */
final class Input
{
    /** @var array<string, mixed>|null */
    private ?array $body = null;

    public function __construct(private readonly Request $request)
    {
    }

    /** @return array<string, mixed> */
    public function body(): array
    {
        if ($this->body === null) {
            $decoded = $this->request->getJsonBody();
            $this->body = is_array($decoded) ? $decoded : [];
        }

        return $this->body;
    }

    public function string(string $name, string $default = ''): string
    {
        $value = $this->body()[$name] ?? null;

        return is_string($value) ? $value : $default;
    }

    public function int(string $name, int $default = 0): int
    {
        return self::toInt($this->body()[$name] ?? null, $default);
    }

    /**
     * Every element of a body list through the int rule; anything but a list reads as empty.
     *
     * @return list<int>
     */
    public function intList(string $name): array
    {
        $values = $this->body()[$name] ?? null;

        return is_array($values) ? array_values(array_map(static fn (mixed $value): int => self::toInt($value, 0), $values)) : [];
    }

    /** The same accessors over one nested object of the body; anything but an object reads as empty. */
    public function object(string $name): self
    {
        $value = $this->body()[$name] ?? null;
        $nested = new self($this->request);
        $nested->body = is_array($value) ? $value : [];

        return $nested;
    }

    public function bool(string $name, bool $default = false): bool
    {
        $value = $this->body()[$name] ?? null;

        return is_bool($value) ? $value : (is_scalar($value) ? filter_var($value, FILTER_VALIDATE_BOOL) : $default);
    }

    public function queryString(string $name, string $default = ''): string
    {
        $value = $this->request->getGetParameter($name);

        return is_string($value) ? $value : $default;
    }

    public function queryInt(string $name, int $default = 0): int
    {
        $value = $this->request->getGetParameter($name);

        return is_string($value) && is_numeric($value) ? (int) $value : $default;
    }

    public function queryPositiveInt(string $name): ?int
    {
        $value = $this->queryInt($name);

        return $value > 0 ? $value : null;
    }

    public function queryBool(string $name): bool
    {
        $value = $this->request->getGetParameter($name);

        return is_string($value) && $value !== '' && $value !== '0';
    }

    public function hasQuery(string $name): bool
    {
        return $this->request->getGetParameter($name) !== null;
    }

    /** @return array<string, mixed>|null */
    public function file(string $name): ?array
    {
        $file = $this->request->getFile($name);

        return is_array($file) ? $file : null;
    }

    private static function toInt(mixed $value, int $default): int
    {
        return is_int($value) || (is_string($value) && is_numeric($value)) ? (int) $value : $default;
    }
}
