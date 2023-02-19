<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\User;

use DR\Review\Controller\App\User\UserAccessTokenController;
use DR\Review\Form\User\AddAccessTokenFormType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @coversDefaultClass \DR\Review\Form\User\AddAccessTokenFormType
 * @covers ::__construct
 */
class AddAccessTokenFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private AddAccessTokenFormType           $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new AddAccessTokenFormType($this->urlGenerator);
    }

    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $url = 'https://123view/user/account-tokens';

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(UserAccessTokenController::class)
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setAction')->with($url);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::exactly(2))
            ->method('add')
            ->withConsecutive(
                ['name', TextType::class],
                ['create', SubmitType::class]
            )->willReturnSelf();

        $this->type->buildForm($builder, []);
    }
}
