<?php
declare(strict_types=1);

namespace DR\Review\Form\Filter;

use DR\Review\Transformer\FilterCollectionTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class InExclusionFilterType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('inclusions', FilterCollectionType::class, ['label' => false]);
        $builder->add('exclusions', FilterCollectionType::class, ['label' => false]);
        $builder->addModelTransformer(new FilterCollectionTransformer());
    }
}
