<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Form\Review;

use DR\GitCommitNotification\Controller\App\Review\Reviewer\AddReviewerController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Form\Review\AddReviewerFormType
 * @covers ::__construct
 */
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

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertNull($introspector->getDefault('review'));
        static::assertSame(['id' => 'form-add-reviewer'], $introspector->getDefault('attr'));
        static::assertSame([CodeReview::class], $introspector->getAllowedTypes('review'));
    }

    /**
     * @covers ::getUserChoices
     * @covers ::buildForm
     */
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

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(AddReviewerController::class, ['id' => 123])
            ->willReturn($url);

        $this->userRepository->expects(self::once())->method('findUsersWithExclusion')->with([789])->willReturn([$user]);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setAction')->with($url);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::once())
            ->method('add')
            ->withConsecutive(
                [
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
                ],
            )->willReturnSelf();

        $this->type->buildForm($builder, ['review' => $review]);
    }

    /**
     * @covers ::getBlockPrefix
     */
    public function testGetBlockPrefix(): void
    {
        static::assertSame('', $this->type->getBlockPrefix());
    }
}
