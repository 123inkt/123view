<?php
declare(strict_types=1);

namespace DR\Review\Serializer;

use Symfony\Component\Form\FormView;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormViewNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const array FORM_PROPERTIES = [
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

    private const array TRANSLATE_PROPERTIES = ['label' => true, 'help' => true];

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

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
            if (isset($data->vars[$propertyKey]) === false) {
                continue;
            }

            // get property value
            $value = $data->vars[$propertyKey];

            // translate specific properties
            if (isset(self::TRANSLATE_PROPERTIES[$propertyKey]) && is_string($value)) {
                $value = $this->translator->trans($value);
            }

            // assign value to normalized data
            $normalizedData['vars'][$propertyKey] = $value;
        }

        // add children
        if (count($data->children) > 0) {
            $normalizedData += $this->normalizer->normalize($data->children, 'json', $context);
        }

        return $normalizedData;
    }
}
