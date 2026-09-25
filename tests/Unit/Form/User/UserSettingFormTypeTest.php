<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\User;

use DR\Review\Controller\App\User\UserSettingController;
use DR\Review\Form\User\UserSettingFormType;
use DR\Review\Form\User\UserSettingType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(UserSettingFormType::class)]
class UserSettingFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private UserSettingFormType              $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new UserSettingFormType($this->urlGenerator);
    }

    public function testBuildForm(): void
    {
        $url = 'https://123view/user/settings';

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(UserSettingController::class)
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setAction')->with($url);
        $builder->expects($this->once())->method('setMethod')->with('POST');
        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(
                ...consecutive(
                    ['setting', UserSettingType::class],
                    ['save', SubmitType::class, ['label' => 'save']],
                )
            )->willReturnSelf();

        $this->type->buildForm($builder, []);
    }
}
