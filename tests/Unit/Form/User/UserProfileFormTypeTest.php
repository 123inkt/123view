<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\User;

use DR\Review\Controller\App\Admin\ChangeUserProfileController;
use DR\Review\Entity\User\User;
use DR\Review\Form\User\UserProfileFormType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(UserProfileFormType::class)]
class UserProfileFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private UserProfileFormType              $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new UserProfileFormType($this->urlGenerator);
    }

    public function testBuildForm(): void
    {
        $url  = 'https://123view/user/profile';
        $user = new User();
        $user->setId(123);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ChangeUserProfileController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setAction')->with($url);
        $builder->expects($this->once())->method('setMethod')->with('POST');
        $builder->expects($this->once())
            ->method('add')
            ->with('roles', ChoiceType::class)
            ->willReturnSelf();
        $builder->expects($this->once())->method('get')->with('roles')->willReturnSelf();

        $this->type->buildForm($builder, ['user' => $user]);
    }

    public function testConfigureOptions(): void
    {
        $this->urlGenerator->expects($this->never())->method('generate');
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertSame(User::class, $introspector->getDefault('data_class'));
        static::assertSame([User::class], $introspector->getAllowedTypes('user'));
    }
}
