<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Review;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Form\Review\CommentTagType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(CommentTagType::class)]
class CommentTagTypeTest extends AbstractTestCase
{
    private TranslatorInterface&MockObject $translator;
    private CommentTagType                 $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->type       = new CommentTagType($this->translator);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->translator->method('trans')->willReturnArgument(0);

        $this->type->configureOptions($resolver);

        static::assertFalse($introspector->getDefault('label'));
        static::assertFalse($introspector->getDefault('required'));

        $choices = [
            'tag.none'           => '',
            'tag.suggestion'     => 'suggestion',
            'tag.nice_to_have'   => 'nice_to_have',
            'tag.change_request' => 'change_request',
            'tag.explanation'    => 'explanation'
        ];
        static::assertSame($choices, $introspector->getDefault('choices'));

        static::assertSame('explanation', $introspector->getDefault('getter')((new Comment())->setTag(CommentTagEnum::Explanation)));

        $comment = new Comment();
        $introspector->getDefault('setter')($comment, 'explanation');
        static::assertSame(CommentTagEnum::Explanation, $comment->getTag());
    }

    public function testGetParent(): void
    {
        static::assertSame(ChoiceType::class, $this->type->getParent());
    }
}
