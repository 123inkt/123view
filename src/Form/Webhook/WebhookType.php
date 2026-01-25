<?php
declare(strict_types=1);

namespace DR\Review\Form\Webhook;

use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Form\Repository\RepositoryChoiceType;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class WebhookType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('url', UrlType::class, ['label' => 'url', 'required' => true]);
        $builder->add(
            'authorization',
            TextType::class,
            [
                'label'    => 'authorization.header',
                'required' => false,
                'getter'   => [$this, 'getAuthorization'],
                'setter'   => [$this, 'setAuthorization'],
            ]
        );
        $builder->add(
            'retries',
            NumberType::class,
            [
                'label'       => 'retries',
                'required'    => true,
                'attr'        => ['min' => 0, 'max' => 10],
                'constraints' => [new Assert\Range(min: 0, max: 10)]
            ]
        );
        $builder->add('enabled', CheckboxType::class, ['label' => 'enabled', 'required' => false]);
        $builder->add('verifySsl', CheckboxType::class, ['label' => 'verify.ssl', 'required' => false]);
        $builder->add('repositories', RepositoryChoiceType::class, ['label' => 'repositories']);

        $builder->get('repositories')->addModelTransformer(new CollectionToArrayTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Webhook::class,]);
    }

    public function getAuthorization(Webhook $webhook): string
    {
        return $webhook->getHeaders()['Authorization'] ?? '';
    }

    public function setAuthorization(Webhook $webhook, ?string $authorization): void
    {
        $webhook->setHeader('Authorization', $authorization);
    }
}
