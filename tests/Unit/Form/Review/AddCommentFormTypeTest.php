<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Review;

use DR\Review\Controller\App\Review\Comment\AddCommentController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Form\Review\AddCommentFormType;
use DR\Review\Form\Review\CommentType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(AddCommentFormType::class)]
class AddCommentFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private AddCommentFormType               $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new AddCommentFormType($this->urlGenerator);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertNull($introspector->getDefault('review'));
        static::assertNull($introspector->getDefault('lineReference'));
        static::assertSame(['null', LineReference::class], $introspector->getAllowedTypes('lineReference'));
        static::assertSame([CodeReview::class], $introspector->getAllowedTypes('review'));
    }

    public function testBuildForm(): void
    {
        $url    = 'https://123view/comment/add';
        $review = new CodeReview();
        $review->setId(123);
        $lineReference = new LineReference('path', 1, 2, 3);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(AddCommentController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setAction')->with($url);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::exactly(3))
            ->method('add')
            ->with(
                ...consecutive(
                    ['lineReference', HiddenType::class, static::isType('array')],
                    ['message', CommentType::class, static::isType('array')],
                    ['save', SubmitType::class, ['label' => 'add.comment']],
                )
            )->willReturnSelf();

        $this->type->buildForm($builder, ['review' => $review, 'lineReference' => $lineReference]);
    }
}
