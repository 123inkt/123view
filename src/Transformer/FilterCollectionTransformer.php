<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DR\GitCommitNotification\Entity\Config\Filter;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Split a filter collection on type. A collection of inclusions and collection of exclusions.
 * @implements DataTransformerInterface<Collection<int, Filter>, array<string, Collection<int, Filter>>>
 */
class FilterCollectionTransformer implements DataTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transform(mixed $value): mixed
    {
        if ($value instanceof Collection === false) {
            return $value;
        }

        $inclusions = new ArrayCollection();
        $exclusions = new ArrayCollection();

        /** @var Filter $filter */
        foreach ($value as $filter) {
            ($filter->isInclusion() === true ? $inclusions : $exclusions)->add($filter);
        }

        return [
            'inclusions' => $inclusions,
            'exclusions' => $exclusions
        ];
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform(mixed $value): mixed
    {
        if (is_array($value) === false) {
            return $value;
        }

        $collection = new ArrayCollection();

        /** @var Filter $filter */
        foreach ($value['inclusions'] ?? new ArrayCollection() as $filter) {
            $filter->setInclusion(true);
            $collection->add($filter);
        }

        /** @var Filter $filter */
        foreach ($value['exclusions'] ?? new ArrayCollection() as $filter) {
            $filter->setInclusion(false);
            $collection->add($filter);
        }

        return $collection;
    }
}
