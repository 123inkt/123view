<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Webhook;

use DR\Review\Controller\App\Admin\WebhookController;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Form\Webhook\EditWebhookFormType;
use DR\Review\Form\Webhook\WebhookType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(EditWebhookFormType::class)]
class EditWebhookFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private EditWebhookFormType              $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new EditWebhookFormType($this->urlGenerator);
    }

    public function testBuildForm(): void
    {
        $url     = 'https://123view/add/webhook';
        $webhook = (new Webhook())->setId(123);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(WebhookController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setAction')->with($url);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::exactly(2))
            ->method('add')
            ->will(
                self::onConsecutiveCalls(
                    [
                        ['repository', WebhookType::class, ['label' => false]],
                        ['save', SubmitType::class, ['label' => 'save']],
                    ]
                )
            )->willReturnSelf();

        $this->type->buildForm($builder, ['data' => ['webhook' => $webhook]]);
    }
}
