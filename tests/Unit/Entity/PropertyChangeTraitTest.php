<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity;

use DR\Review\Entity\PropertyChangeTrait;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Tests\Helper\Entity\MockPropertyChangeObject;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PropertyChangeTrait::class)]
class PropertyChangeTraitTest extends AbstractTestCase
{
    private MockPropertyChangeObject $objectWithTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectWithTrait = new MockPropertyChangeObject();
    }

    public function testAccessors(): void
    {
        static::assertFalse($this->objectWithTrait->isPropertyChanged('prop'));

        $this->objectWithTrait->proxyPropertyChange('prop', 'foo', 'bar');
        static::assertTrue($this->objectWithTrait->isPropertyChanged('prop'));
        static::assertSame('foo', $this->objectWithTrait->getOriginalValue('prop'));
        static::assertSame('bar', $this->objectWithTrait->getChangedValue('prop'));

        $this->objectWithTrait->proxyPropertyChange('prop', 'bar', 'changed');
        static::assertTrue($this->objectWithTrait->isPropertyChanged('prop'));
        static::assertSame('foo', $this->objectWithTrait->getOriginalValue('prop'));
        static::assertSame('changed', $this->objectWithTrait->getChangedValue('prop'));

        $this->objectWithTrait->proxyPropertyChange('prop', 'changed', 'foo');
        static::assertFalse($this->objectWithTrait->isPropertyChanged('prop'));
    }
}
