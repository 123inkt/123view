<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Review;

use DR\Review\Controller\App\Review\Reviewer\AddReviewerController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\AddReviewerFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(AddReviewerFormType::class)]
class AddReviewerFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private UserRepository&MockObject        $userRepository;
    private User                             $user;
    private AddReviewerFormType              $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator   = $this->createMock(UrlGeneratorInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->user           = new User();
        $this->type           = new AddReviewerFormType($this->urlGenerator, $this->userRepository, $this->user);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertNull($introspector->getDefault('review'));
        static::assertSame([CodeReview::class], $introspector->getAllowedTypes('review'));
    }

    public function testBuildForm(): void
    {
        $url = 'https://123view/reviewer/add';

        $user = new User();
        $user->setId(789);
        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);

        $review = new CodeReview();
        $review->setId(123);
        $review->getReviewers()->add($reviewer);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(AddReviewerController::class, ['id' => 123])
            ->willReturn($url);

        $this->userRepository->expects($this->once())->method('findUsersWithExclusion')->with([789])->willReturn([$user]);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setAction')->with($url);
        $builder->expects($this->once())->method('setMethod')->with('POST');
        $builder->expects($this->once())
            ->method('add')
            ->with(
                'user',
                ChoiceType::class,
                static::callback(function ($options) use ($user) {
                    static::assertFalse($options['required']);
                    static::assertSame('add.reviewer', $options['placeholder']);
                    static::assertFalse($options['label']);
                    static::assertFalse($options['choice_translation_domain']);
                    static::assertSame([$user], $options['choices']);
                    static::assertSame([$this->user], $options['preferred_choices']);
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
