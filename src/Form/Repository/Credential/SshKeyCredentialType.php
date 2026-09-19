<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository\Credential;

use DR\Review\Entity\Repository\Credential\SshKeyCredential;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<SshKeyCredential>
 */
class SshKeyCredentialType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('privateKey', TextareaType::class, ['required' => false, 'label' => 'private.key', 'setter' => [$this, 'setPrivateKey']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SshKeyCredential::class]);
    }

    public function setPrivateKey(SshKeyCredential $credential, ?string $value): void
    {
        if ($value !== null && $value !== '') {
            $credential->setPrivateKey($value);
        }
    }
}
