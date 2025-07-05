<?php
declare(strict_types=1);

namespace DR\Review\Serializer;

use Symfony\Component\Form\FormView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FormViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const FORM_PROPERTIES = [
        'action',
        'attr',
        'block_prefixes',
        'disabled',
        'full_name',
        'help',
        'help_attr',
        'help_html',
        'id',
        'label',
        'label_attr',
        'label_html',
        'method',
        'name',
        'required',
        'unique_block_prefix',
        'value',
    ];

    /**
     * @inheritDoc
     */
    public function getSupportedTypes(?string $format): array
    {
        return [FormView::class => true];
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FormView && $format === 'json';
    }

    /**
     * @inheritDoc
     *
     * @param FormView $data
     *
     * @return array<string, mixed>
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $normalizedData = ['vars' => []];
        foreach (self::FORM_PROPERTIES as $propertyKey) {
            if (isset($data->vars[$propertyKey])) {
                $normalizedData['vars'][$propertyKey] = $data->vars[$propertyKey];
            }
        }

        if (count($data->children) > 0) {
            $normalizedData += $this->normalizer->normalize($data->children, 'json', $context);
        }

        return $normalizedData;
    }
}
