<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Form\User;

use DR\GitCommitNotification\Controller\App\User\UserSettingController;
use DR\GitCommitNotification\Form\User\UserSettingFormType;
use DR\GitCommitNotification\Form\User\UserSettingType;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Form\User\UserSettingFormType
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
        $url = 'https://commit-notification/user/settings';

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(UserSettingController::class)
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setAction')->with($url);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::exactly(2))
            ->method('add')
            ->withConsecutive(
                ['setting', UserSettingType::class],
                ['save', SubmitType::class, ['label' => 'Save']],
            )->willReturnSelf();

        $this->type->buildForm($builder, []);
    }
}
