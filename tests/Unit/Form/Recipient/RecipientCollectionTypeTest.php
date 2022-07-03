<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Form\Recipient;

use DR\GitCommitNotification\Entity\Config\Recipient;
use DR\GitCommitNotification\Form\Recipient\RecipientCollectionType;
use DR\GitCommitNotification\Form\Recipient\RecipientType;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Form\Recipient\RecipientCollectionType
 */
class RecipientCollectionTypeTest extends AbstractTestCase
{
    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RecipientCollectionType();
        $type->configureOptions($resolver);

        static::assertSame(RecipientType::class, $introspector->getDefault('entry_type'));
        static::assertTrue($introspector->getDefault('allow_add'));
        static::assertTrue($introspector->getDefault('allow_delete'));
        static::assertTrue($introspector->getDefault('prototype'));
        static::assertFalse($introspector->getDefault('by_reference'));

        $deleteEmpty = $introspector->getDefault('delete_empty');
        static::assertIsCallable($deleteEmpty);
        static::assertTrue($deleteEmpty((new Recipient())));
        static::assertFalse($deleteEmpty((new Recipient())->setEmail('email')));

        $constraints = $introspector->getDefault('constraints');
        static::assertIsArray($constraints);
        static::assertCount(1, $constraints);
    }

    /**
     * @covers ::getParent
     */
    public function testGetParent(): void
    {
        $type = new RecipientCollectionType();
        static::assertSame(CollectionType::class, $type->getParent());
    }
}
