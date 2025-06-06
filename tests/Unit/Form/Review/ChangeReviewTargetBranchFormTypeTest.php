<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Review;

use DR\Review\Controller\App\Review\ChangeTargetBranchController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Form\Review\ChangeReviewTargetBranchFormType;
use DR\Review\Service\Git\Branch\CacheableGitBranchService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(ChangeReviewTargetBranchFormType::class)]
class ChangeReviewTargetBranchFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject     $urlGenerator;
    private CacheableGitBranchService&MockObject $branchService;
    private ChangeReviewTargetBranchFormType     $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator  = $this->createMock(UrlGeneratorInterface::class);
        $this->branchService = $this->createMock(CacheableGitBranchService::class);
        $this->type          = new ChangeReviewTargetBranchFormType($this->urlGenerator, $this->branchService);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertNull($introspector->getDefault('review'));
        static::assertSame(CodeReview::class, $introspector->getDefault('data_class'));
        static::assertSame([CodeReview::class], $introspector->getAllowedTypes('review'));
    }

    public function testBuildForm(): void
    {
        $url = 'https://123view/review/target-branch';

        $repository = new Repository();
        $review     = (new CodeReview())->setId(123);
        $review->setRepository($repository);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ChangeTargetBranchController::class, ['id' => 123])
            ->willReturn($url);

        $this->branchService->expects($this->once())->method('getRemoteBranches')->with($repository)->willReturn(['origin/branch']);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setAction')->with($url);
        $builder->expects($this->once())->method('setMethod')->with('POST');
        $builder->expects($this->once())
            ->method('add')
            ->with(
                'targetBranch',
                ChoiceType::class,
                static::callback(function ($options) {
                    static::assertFalse($options['required']);
                    static::assertFalse($options['label']);
                    static::assertSame(['→ branch' => 'branch'], $options['choices']);
                    static::assertFalse($options['multiple']);
                    static::assertFalse($options['expanded']);

                    return true;
                })
            )
            ->willReturnSelf();

        $this->type->buildForm($builder, ['review' => $review]);
    }

    public function testGetBlockPrefix(): void
    {
        static::assertSame('', $this->type->getBlockPrefix());
    }
}
