<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository\Credential;

use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\RepositoryCredential;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Listens to:
 * - FormEvents::PRE_SET_DATA
 * - FormEvents::PRE_SUBMIT
 */
class CredentialTypeListener
{
    public function __invoke(FormEvent $event): void
    {
        $data = $event->getData();

        if ($data instanceof RepositoryCredential) {
            $authType = $data->getAuthType();
        } elseif (is_array($data)) {
            $authType = is_string($data['authType'] ?? null) ? $data['authType'] : AuthenticationType::BASIC_AUTH;
        } else {
            $authType = AuthenticationType::BASIC_AUTH;
        }

        if ($authType === AuthenticationType::SSH_KEY) {
            $event->getForm()->add('credentials', SshKeyCredentialType::class, ['label' => false]);
        } else {
            $event->getForm()->add('credentials', BasicAuthCredentialType::class, ['label' => false]);
        }
    }
}
