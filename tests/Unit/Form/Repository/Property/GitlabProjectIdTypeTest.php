<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository\Property;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Form\Repository\Property\GitlabProjectIdType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(GitlabProjectIdType::class)]
class GitlabProjectIdTypeTest extends AbstractTestCase
{
    public function testGetParent(): void
    {
        static::assertSame(IntegerType::class, (new GitlabProjectIdType())->getParent());
    }

    public function testSetProperty(): void
    {
        $repositoryA = new Repository();
        $repositoryB = new Repository();
        $repositoryB->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '10'));

        $type = new GitlabProjectIdType();
        $type->setProperty($repositoryA, 10);
        $type->setProperty($repositoryB, null);

        static::assertSame(10, $type->getProperty($repositoryA));
        static::assertNull($type->getProperty($repositoryB));
    }

    public function testGetProperty(): void
    {
        $repositoryA = new Repository();
        $repositoryB = new Repository();
        $repositoryB->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '10'));

        $type = new GitlabProjectIdType();
        static::assertNull($type->getProperty($repositoryA));
        static::assertSame(10, $type->getProperty($repositoryB));
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new GitlabProjectIdType();
        $type->configureOptions($resolver);

        static::assertSame('gitlab.project.id', $introspector->getDefault('label'));
        static::assertSame('gitlab.project.id.help', $introspector->getDefault('help'));
        static::assertFalse($introspector->getDefault('required'));
        static::assertSame([$type, 'getProperty'], $introspector->getDefault('getter'));
        static::assertSame([$type, 'setProperty'], $introspector->getDefault('setter'));
    }
}
