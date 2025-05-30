<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Form\Repository\RepositoryChoiceType;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(RepositoryChoiceType::class)]
class RepositoryChoiceTypeTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(RepositoryRepository::class);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);
        $repositories = [new Repository()];

        $this->repository->expects($this->once())->method('findBy')->with(['active' => 1], ['name' => 'ASC'])->willReturn($repositories);

        $type = new RepositoryChoiceType($this->repository);
        $type->configureOptions($resolver);

        static::assertSame($repositories, $introspector->getDefault('choices'));
        static::assertSame('id', $introspector->getDefault('choice_value'));

        static::assertFalse($introspector->getDefault('choice_translation_domain'));
        static::assertTrue($introspector->getDefault('multiple'));
        static::assertTrue($introspector->getDefault('expanded'));

        $choiceLabel = $introspector->getDefault('choice_label');
        static::assertIsCallable($choiceLabel);
        static::assertSame('', $choiceLabel(null));
        static::assertSame('name', $choiceLabel((new Repository())->setName('name')));

        $constraints = $introspector->getDefault('constraints');
        static::assertIsArray($constraints);
        static::assertCount(1, $constraints);
    }

    public function testGetParent(): void
    {
        $type = new RepositoryChoiceType($this->repository);
        static::assertSame(ChoiceType::class, $type->getParent());
    }
}
