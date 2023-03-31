<?php

declare(strict_types=1);

namespace DR\Review\Entity;

trait PropertyChangeTrait
{
    /** @var array<string, mixed> */
    private array $originalProperties = [];

    /** @var array<string, mixed> */
    private array $changedProperties = [];

    public function isPropertyChanged(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->changedProperties);
    }

    public function getOriginalValue(string $propertyName): mixed
    {
        return $this->originalProperties[$propertyName] ?? null;
    }

    public function getChangedValue(string $propertyName): mixed
    {
        return $this->changedProperties[$propertyName];
    }

    /**
     * @template T
     *
     * @param T $newValue
     *
     * @return T
     */
    protected function propertyChange(string $propertyName, mixed $oldValue, mixed $newValue): mixed
    {
        if ($oldValue === $newValue) {
            return $newValue;
        }

        if (isset($this->originalProperties[$propertyName]) === false) {
            $this->originalProperties[$propertyName] = $oldValue;
        } elseif ($this->originalProperties[$propertyName] === $newValue) {
            unset($this->originalProperties[$propertyName]);
            unset($this->changedProperties[$propertyName]);

            return $newValue;
        }

        $this->changedProperties[$propertyName] = $newValue;

        return $newValue;
    }
}
