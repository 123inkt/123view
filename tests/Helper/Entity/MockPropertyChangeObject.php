<?php
declare(strict_types=1);

namespace DR\Review\Tests\Helper\Entity;

use DR\Review\Entity\PropertyChangeTrait;

class MockPropertyChangeObject
{
    use PropertyChangeTrait;

    /**
     * @template T
     *
     * @param T $newValue
     *
     * @return T
     */
    public function proxyPropertyChange(string $propertyName, mixed $oldValue, mixed $newValue): mixed
    {
        return $this->propertyChange($propertyName, $oldValue, $newValue);
    }
}
