<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Webhook;

use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Form\Repository\RepositoryChoiceType;
use DR\Review\Form\Webhook\WebhookType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(WebhookType::class)]
class WebhookTypeTest extends AbstractTestCase
{
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(6))
            ->method('add')
            ->with(
                ...consecutive(
                    ['url', UrlType::class],
                    ['authorization', TextType::class],
                    ['retries', NumberType::class],
                    ['enabled', CheckboxType::class],
                    ['verifySsl', CheckboxType::class],
                    ['repositories', RepositoryChoiceType::class],
                )
            )->willReturnSelf();
        $builder->expects($this->once())->method('get')->with('repositories')->willReturnSelf();

        $type = new WebhookType();
        $type->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new WebhookType();
        $type->configureOptions($resolver);

        static::assertSame(Webhook::class, $introspector->getDefault('data_class'));
    }

    public function testGetAuthorization(): void
    {
        $webhookA = new Webhook();
        $webhookB = (new Webhook())->setHeader('Authorization', 'Bearer 123view');

        $type = new WebhookType();
        static::assertSame('', $type->getAuthorization($webhookA));
        static::assertSame('Bearer 123view', $type->getAuthorization($webhookB));
    }

    public function testSetAuthorization(): void
    {
        $webhookA = new Webhook();
        $webhookB = (new Webhook())->setHeader('Authorization', 'Bearer 123view');

        $type = new WebhookType();
        $type->setAuthorization($webhookA, 'bearer');
        $type->setAuthorization($webhookB, null);

        static::assertSame(['Authorization' => 'bearer'], $webhookA->getHeaders());
        static::assertSame([], $webhookB->getHeaders());
    }
}
