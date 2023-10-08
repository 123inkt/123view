<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository;

use ApiPlatform\Api\UrlGeneratorInterface;
use DR\Review\Controller\App\Admin\Credentials\CredentialsController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Form\Repository\Property\GitlabProjectIdType;
use DR\Review\Form\Repository\RepositoryType;
use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(RepositoryType::class)]
class RepositoryTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject          $urlGenerator;
    private RepositoryCredentialRepository&MockObject $credentialRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator         = $this->createMock(UrlGeneratorInterface::class);
        $this->credentialRepository = $this->createMock(RepositoryCredentialRepository::class);
    }

    public function testBuildForm(): void
    {
        $this->urlGenerator->expects(self::once())->method('generate')->with(CredentialsController::class)->willReturn('url');
        $this->credentialRepository->expects(self::once())->method('findBy')->with([], ['name' => 'ASC'])->willReturn([]);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::exactly(10))
            ->method('add')
            ->with(
                ...consecutive(
                    ['active', CheckboxType::class],
                    ['favorite', CheckboxType::class],
                    ['name', TextType::class],
                    ['displayName', TextType::class],
                    ['mainBranchName', TextType::class],
                    ['url', UrlType::class],
                    ['credential', ChoiceType::class],
                    ['updateRevisionsInterval', IntegerType::class],
                    ['validateRevisionsInterval', IntegerType::class],
                    ['gitlabProjectId', GitlabProjectIdType::class],
                )
            )->willReturnSelf();
        $builder->expects(self::once())->method('get')->with('url')->willReturnSelf();

        $type = new RepositoryType($this->urlGenerator, $this->credentialRepository, 'gitlab');
        $type->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RepositoryType($this->urlGenerator, $this->credentialRepository, 'gitlab');
        $type->configureOptions($resolver);

        static::assertSame(Repository::class, $introspector->getDefault('data_class'));
    }
}
