<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Admin;

use DR\Review\Entity\Webhook\Webhook;
use Symfony\Component\Form\FormView;

class EditWebhookViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly Webhook $webhook, public readonly FormView $form)
    {
    }
}
