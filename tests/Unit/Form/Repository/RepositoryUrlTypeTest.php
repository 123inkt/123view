<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Form\Repository\RepositoryUrlType;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Transformer\RepositoryUrlTransformer;
use League\Uri\Uri;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\Review\Form\Repository\RepositoryUrlType
 */
class RepositoryUrlTypeTest extends AbstractTestCase
{
    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::exactly(3))
            ->method('add')
            ->will(
                self::onConsecutiveCalls(
                    ['url', UrlType::class],
                    ['username', TextType::class],
                    ['password', PasswordType::class],
                )
            )->willReturnSelf();
        $builder->expects(self::once())->method('addModelTransformer')->with(static::isInstanceOf(RepositoryUrlTransformer::class));

        $type = new RepositoryUrlType();
        $type->buildForm($builder, []);
    }

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RepositoryUrlType();
        $type->configureOptions($resolver);

        $callback = $introspector->getDefault('setter');
        static::assertIsCallable($callback);

        // test callback
        $repository = new Repository();
        $repository->setUrl(Uri::createFromString('https://sherlock:holmes@example.com'));
        $uri = Uri::createFromString('https://watson@example.com');
        $callback($repository, $uri);

        static::assertSame('https://watson:holmes@example.com', (string)$repository->getUrl());
    }
}
