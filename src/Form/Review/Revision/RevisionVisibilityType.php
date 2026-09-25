<?php
declare(strict_types=1);

namespace DR\Review\Form\Review\Revision;

use DR\Review\Entity\Revision\RevisionVisibility;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<RevisionVisibility>
 */
class RevisionVisibilityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'form_id'    => null,
                'data_class' => RevisionVisibility::class,
            ]
        );
        $resolver->addAllowedTypes('form_id', ['string']);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'visible',
            CheckboxType::class,
            [
                'label'              => false,
                'translation_domain' => false,
                'required'           => false,
                'attr'               => [
                    'form'                                 => $options['form_id'],
                    'data-component--icon-checkbox-target' => 'checkbox',
                    'data-role'                            => 'visibility'
                ],
            ]
        );
    }
}
