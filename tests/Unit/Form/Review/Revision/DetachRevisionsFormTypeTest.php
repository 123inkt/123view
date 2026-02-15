<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Review\Revision;

use DR\Review\Controller\App\Revision\DetachRevisionController;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Form\Review\Revision\DetachRevisionsFormType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(DetachRevisionsFormType::class)]
class DetachRevisionsFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private DetachRevisionsFormType          $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new DetachRevisionsFormType($this->urlGenerator);
    }

    public function testConfigureOptions(): void
    {
        $this->urlGenerator->expects($this->never())->method('generate');
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertNull($introspector->getDefault('reviewId'));
        static::assertNull($introspector->getDefault('revisions'));
        static::assertSame(['array'], $introspector->getAllowedTypes('revisions'));
        static::assertSame(['int'], $introspector->getAllowedTypes('reviewId'));
    }

    public function testBuildForm(): void
    {
        $url      = 'https://123view/detach/revision';
        $revision = new Revision();
        $revision->setId(456);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(DetachRevisionController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setAction')->with($url);
        $builder->expects($this->once())->method('setMethod')->with('POST');
        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(
                ...consecutive(
                    ['rev456', CheckboxType::class],
                    ['detach', SubmitType::class, ['label' => 'detach.revisions']],
                )
            )->willReturnSelf();

        $this->type->buildForm($builder, ['reviewId' => 123, 'revisions' => [$revision]]);
    }

    public function testGetBlockPrefix(): void
    {
        $this->urlGenerator->expects($this->never())->method('generate');
        static::assertSame('', $this->type->getBlockPrefix());
    }
}
