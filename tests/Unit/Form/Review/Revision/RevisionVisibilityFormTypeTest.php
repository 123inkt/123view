<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Review\Revision;

use DR\Review\Controller\App\Revision\UpdateRevisionVisibilityController;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Form\Review\Revision\RevisionVisibilityFormType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @coversDefaultClass \DR\Review\Form\Review\Revision\RevisionVisibilityFormType
 * @covers ::__construct
 */
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

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertNull($introspector->getDefault('reviewId'));
        static::assertSame(['id' => 'revision-visibility-form'], $introspector->getDefault('attr'));
        static::assertSame(['int'], $introspector->getAllowedTypes('reviewId'));
    }

    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $url      = 'https://123view/revision/visibility';
        $revision = new Revision();
        $revision->setId(456);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(UpdateRevisionVisibilityController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setAction')->with($url);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::exactly(2))
            ->method('add')
            ->withConsecutive(
                ['hidden', HiddenType::class],
                ['visibilities', CollectionType::class],
            )->willReturnSelf();

        $this->type->buildForm($builder, ['reviewId' => 123]);
    }

    /**
     * @covers ::getBlockPrefix
     */
    public function testGetBlockPrefix(): void
    {
        static::assertSame('', $this->type->getBlockPrefix());
    }
}
