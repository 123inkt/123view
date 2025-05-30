<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository\Credential;

use DR\Review\Controller\App\Admin\Credentials\CredentialController;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Form\Repository\Credential\EditCredentialFormType;
use DR\Review\Form\Repository\Credential\RepositoryCredentialType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(EditCredentialFormType::class)]
class EditCredentialFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private EditCredentialFormType           $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new EditCredentialFormType($this->urlGenerator);
    }

    public function testBuildForm(): void
    {
        $url        = 'https://123view/add/credential';
        $credential = (new RepositoryCredential())->setId(123);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(CredentialController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setAction')->with($url);
        $builder->expects($this->once())->method('setMethod')->with('POST');
        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(
                ...consecutive(
                    ['credential', RepositoryCredentialType::class, ['label' => false]],
                    ['save', SubmitType::class, ['label' => 'save']],
                )
            )->willReturnSelf();

        $this->type->buildForm($builder, ['data' => ['credential' => $credential]]);
    }
}
