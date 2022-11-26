<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Form\Recipient;

use DR\GitCommitNotification\Entity\Config\Recipient;
use DR\GitCommitNotification\Form\Recipient\RecipientType;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Form\Recipient\RecipientType
 */
class RecipientTypeTest extends AbstractTestCase
{
    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::exactly(2))
            ->method('add')
            ->withConsecutive(
                ['email', EmailType::class],
                ['name', TextType::class],
            )->willReturnSelf();

        $type = new RecipientType();
        $type->buildForm($builder, []);
    }

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RecipientType();
        $type->configureOptions($resolver);

        static::assertSame(Recipient::class, $introspector->getDefault('data_class'));
    }
}
