<?php
declare(strict_types=1);

namespace DR\Review\Form\Recipient;

use DR\Review\Entity\Notification\Recipient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Recipient>
 */
class RecipientType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, ['attr' => ['placeholder' => 'Email']]);
        $builder->add('name', TextType::class, ['required' => false, 'attr' => ['maxlength' => 255, 'placeholder' => 'name']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Recipient::class]);
    }
}
