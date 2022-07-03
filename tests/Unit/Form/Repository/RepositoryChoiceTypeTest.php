<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Form\Repository;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Form\Repository\RepositoryChoiceType;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Form\Repository\RepositoryChoiceType
 * @covers ::__construct
 */
class RepositoryChoiceTypeTest extends AbstractTestCase
{
    /** @var RepositoryRepository&MockObject */
    private RepositoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(RepositoryRepository::class);
    }

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);
        $repositories = [new Repository()];

        $this->repository->expects(self::once())->method('findBy')->with([], ['name' => 'ASC'])->willReturn($repositories);

        $type = new RepositoryChoiceType($this->repository);
        $type->configureOptions($resolver);

        static::assertSame(['class' => 'checkbox-inline'], $introspector->getDefault('label_attr'));
        static::assertSame($repositories, $introspector->getDefault('choices'));
        static::assertSame('id', $introspector->getDefault('choice_value'));

        static::assertFalse($introspector->getDefault('choice_translation_domain'));
        static::assertTrue($introspector->getDefault('multiple'));
        static::assertTrue($introspector->getDefault('expanded'));

        $choiceLabel = $introspector->getDefault('choice_label');
        static::assertIsCallable($choiceLabel);
        static::assertSame('', $choiceLabel(new Repository()));
        static::assertSame('name', $choiceLabel((new Repository())->setName('name')));

        $constraints = $introspector->getDefault('constraints');
        static::assertIsArray($constraints);
        static::assertCount(1, $constraints);
    }

    /**
     * @covers ::getParent
     */
    public function testGetParent(): void
    {
        $type = new RepositoryChoiceType($this->repository);
        static::assertSame(ChoiceType::class, $type->getParent());
    }
}
