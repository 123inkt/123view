<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Review;

use DR\Review\Controller\App\Review\Comment\AddCommentReplyController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Form\Review\AddCommentReplyFormType;
use DR\Review\Form\Review\CommentType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @coversDefaultClass \DR\Review\Form\Review\AddCommentReplyFormType
 * @covers ::__construct
 */
class AddCommentReplyFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private AddCommentReplyFormType          $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new AddCommentReplyFormType($this->urlGenerator);
    }

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertNull($introspector->getDefault('comment'));
        static::assertSame([Comment::class], $introspector->getAllowedTypes('comment'));
    }

    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $url     = 'https://123view/comment/reply';
        $comment = new Comment();
        $comment->setId(123);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(AddCommentReplyController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setAction')->with($url);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::exactly(2))
            ->method('add')
            ->withConsecutive(
                ['message', CommentType::class],
                ['save', SubmitType::class, ['label' => 'reply']],
            )->willReturnSelf();

        $this->type->buildForm($builder, ['comment' => $comment]);
    }
}
