<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Review\Revision;

use DR\Review\Controller\App\Revision\UpdateRevisionVisibilityController;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Form\Review\Revision\RevisionVisibilityFormType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(RevisionVisibilityFormType::class)]
class RevisionVisibilityFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private RevisionVisibilityFormType       $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new RevisionVisibilityFormType($this->urlGenerator);
    }

    public function testConfigureOptions(): void
    {
        $this->urlGenerator->expects($this->never())->method('generate');
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertNull($introspector->getDefault('reviewId'));
        static::assertSame(['id' => 'revision-visibility-form'], $introspector->getDefault('attr'));
        static::assertSame(['int'], $introspector->getAllowedTypes('reviewId'));
    }

    public function testBuildForm(): void
    {
        $url      = 'https://123view/revision/visibility';
        $revision = new Revision();
        $revision->setId(456);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(UpdateRevisionVisibilityController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setAction')->with($url);
        $builder->expects($this->once())->method('setMethod')->with('POST');
        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(
                ...consecutive(
                    ['hidden', HiddenType::class],
                    ['visibilities', CollectionType::class],
                )
            )->willReturnSelf();

        $this->type->buildForm($builder, ['reviewId' => 123]);
    }

    public function testGetBlockPrefix(): void
    {
        $this->urlGenerator->expects($this->never())->method('generate');
        static::assertSame('', $this->type->getBlockPrefix());
    }
}
