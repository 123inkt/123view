<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\User;

use DR\Review\Controller\App\User\UserSettingController;
use DR\Review\Form\User\UserSettingFormType;
use DR\Review\Form\User\UserSettingType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

/**
 * @coversDefaultClass \DR\Review\Form\User\UserSettingFormType
 * @covers ::__construct
 */
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

    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $url = 'https://123view/user/settings';

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(UserSettingController::class)
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setAction')->with($url);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::exactly(2))
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
