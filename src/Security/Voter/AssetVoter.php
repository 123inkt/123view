<?php
declare(strict_types=1);

namespace DR\Review\Security\Voter;

use DR\Review\Entity\Asset\Asset;
use DR\Utils\Assert;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Asset>
 */
class AssetVoter extends Voter
{
    public const VIEW = 'VIEW';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // only support assets, and correct attributes
        return $this->requestStack->getCurrentRequest() !== null && $subject instanceof Asset && $attribute === self::VIEW;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $hash  = Assert::notNull($this->requestStack->getCurrentRequest())->query->getString('hash');
        $asset = Assert::isInstanceOf($subject, Asset::class);

        return $hash !== '' && hash_equals($asset->getHash(), $hash);
    }
}
