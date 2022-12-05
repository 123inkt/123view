<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Review;

use DR\Review\Form\Review\CommentType;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @coversDefaultClass \DR\Review\Form\Review\CommentType
 */
class CommentTypeTest extends AbstractTestCase
{
    private CommentType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->type = new CommentType();
    }

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertFalse($introspector->getDefault('label'));
        static::assertSame(['autocomplete' => 'off'], $introspector->getDefault('attr'));
        static::assertEquals(new Assert\Length(max: 2000), $introspector->getDefault('constraints'));
    }

    /**
     * @covers ::getParent
     */
    public function testGetParent(): void
    {
        static::assertSame(TextareaType::class, $this->type->getParent());
    }
}
