<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository;

use DR\Review\Controller\App\Admin\RepositoryController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Form\Repository\EditRepositoryFormType;
use DR\Review\Form\Repository\RepositoryType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(EditRepositoryFormType::class)]
class EditRepositoryFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private EditRepositoryFormType           $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new EditRepositoryFormType($this->urlGenerator);
    }

    public function testBuildForm(): void
    {
        $url        = 'https://123view/add/repository';
        $repository = (new Repository())->setId(123);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(RepositoryController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setAction')->with($url);
        $builder->expects($this->once())->method('setMethod')->with('POST');
        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(
                ...consecutive(
                    ['repository', RepositoryType::class],
                    ['save', SubmitType::class, ['label' => 'save']],
                )
            )->willReturnSelf();

        $this->type->buildForm($builder, ['data' => ['repository' => $repository]]);
    }
}
