<?php
declare(strict_types=1);

namespace DR\Review\Form\Webhook;

use DR\Review\Controller\App\Admin\Webhook\WebhookController;
use DR\Review\Entity\Webhook\Webhook;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EditWebhookFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array{webhook: Webhook|null} $data */
        $data = $options['data'];

        $builder->setAction($this->urlGenerator->generate(WebhookController::class, ['id' => $data['webhook']?->getId()]));
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('webhook', WebhookType::class, ['label' => false]);
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }
}
