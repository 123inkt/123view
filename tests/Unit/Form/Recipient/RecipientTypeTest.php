<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Recipient;

use DR\Review\Entity\Notification\Recipient;
use DR\Review\Form\Recipient\RecipientType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(RecipientType::class)]
class RecipientTypeTest extends AbstractTestCase
{
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(
                ...consecutive(
                    ['email', EmailType::class],
                    ['name', TextType::class],
                )
            )->willReturnSelf();

        $type = new RecipientType();
        $type->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RecipientType();
        $type->configureOptions($resolver);

        static::assertSame(Recipient::class, $introspector->getDefault('data_class'));
    }
}
