<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Review;

use DR\Review\Controller\App\Review\Comment\UpdateCommentReplyController;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Form\Review\CommentTagType;
use DR\Review\Form\Review\CommentType;
use DR\Review\Form\Review\EditCommentReplyFormType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(EditCommentReplyFormType::class)]
class EditCommentReplyFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private EditCommentReplyFormType         $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new EditCommentReplyFormType($this->urlGenerator);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertNull($introspector->getDefault('reply'));
        static::assertSame(CommentReply::class, $introspector->getDefault('data_class'));
    }

    public function testBuildForm(): void
    {
        $url   = 'https://123view/comment-reply/update';
        $reply = new CommentReply();
        $reply->setId(123);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(UpdateCommentReplyController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setAction')->with($url);
        $builder->expects($this->once())->method('setMethod')->with('POST');
        $builder->expects($this->exactly(3))
            ->method('add')
            ->with(
                ...consecutive(
                    ['message', CommentType::class],
                    ['tag', CommentTagType::class],
                    ['save', SubmitType::class, ['label' => 'save']],
                )
            )->willReturnSelf();

        $this->type->buildForm($builder, ['reply' => $reply]);
    }
}
