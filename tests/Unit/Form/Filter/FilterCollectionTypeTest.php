<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Filter;

use DR\Review\Entity\Notification\Filter;
use DR\Review\Form\Filter\FilterCollectionType;
use DR\Review\Form\Filter\FilterType;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\Review\Form\Filter\FilterCollectionType
 */
class FilterCollectionTypeTest extends AbstractTestCase
{
    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new FilterCollectionType();
        $type->configureOptions($resolver);

        static::assertSame(FilterType::class, $introspector->getDefault('entry_type'));
        static::assertTrue($introspector->getDefault('allow_add'));
        static::assertTrue($introspector->getDefault('allow_delete'));
        static::assertTrue($introspector->getDefault('prototype'));

        $deleteEmpty = $introspector->getDefault('delete_empty');
        static::assertIsCallable($deleteEmpty);
        static::assertTrue($deleteEmpty(null));
        static::assertFalse($deleteEmpty((new Filter())->setPattern('pattern')));

        $constraints = $introspector->getDefault('constraints');
        static::assertIsArray($constraints);
        static::assertCount(1, $constraints);
    }

    /**
     * @covers ::getParent
     */
    public function testGetParent(): void
    {
        $type = new FilterCollectionType();
        static::assertSame(CollectionType::class, $type->getParent());
    }
}
