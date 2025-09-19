<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\Voter;

use DR\Review\Entity\Asset\Asset;
use DR\Review\Security\Voter\AssetVoter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(AssetVoter::class)]
class AssetVoterTest extends AbstractTestCase
{
    private TokenInterface&MockObject $token;
    private RequestStack              $requestStack;
    private AssetVoter                $voter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestStack = new RequestStack();
        $this->token        = $this->createMock(TokenInterface::class);
        $this->voter        = new AssetVoter($this->requestStack);
    }

    #[TestWith(['VIEW', VoterInterface::ACCESS_GRANTED])]
    #[TestWith(['DELETE', VoterInterface::ACCESS_ABSTAIN])]
    #[TestWith(['foobar', VoterInterface::ACCESS_ABSTAIN])]
    public function testSupportsWithDifferentAttributes(string $attribute, int $accessGrant): void
    {
        $asset   = (new Asset())->setData('test-data');
        $request = new Request(['hash' => $asset->getHash()]);
        $this->requestStack->push($request);

        $result = $this->voter->vote($this->token, $asset, [$attribute]);

        static::assertSame($accessGrant, $result);
    }

    #[TestWith([new Asset(), VoterInterface::ACCESS_GRANTED])]
    #[TestWith(['string', VoterInterface::ACCESS_ABSTAIN])]
    #[TestWith([123, VoterInterface::ACCESS_ABSTAIN])]
    #[TestWith([null, VoterInterface::ACCESS_ABSTAIN])]
    #[TestWith([false, VoterInterface::ACCESS_ABSTAIN])]
    public function testSupportsWithDifferentSubjects(mixed $subject, int $accessGrant): void
    {
        if ($subject instanceof Asset) {
            $subject->setData('test-data');
            $hash = $subject->getHash();
        } else {
            $hash = 'invalid-hash';
        }

        $request = new Request(['hash' => $hash]);
        $this->requestStack->push($request);

        $result = $this->voter->vote($this->token, $subject, [AssetVoter::VIEW]);

        static::assertSame($accessGrant, $result);
    }

    public function testSupportsWithoutRequest(): void
    {
        $asset = new Asset();
        $asset->setData('test-data');

        $token = $this->createMock(TokenInterface::class);

        $result = $this->voter->vote($token, $asset, [AssetVoter::VIEW]);
        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testVoteOnAttributeWithMatchingHash(): void
    {
        $asset = new Asset();
        $asset->setData('test-data-content');
        $correctHash = $asset->getHash();

        $request = new Request(['hash' => $correctHash]);
        $this->requestStack->push($request);

        $token = $this->createMock(TokenInterface::class);

        $result = $this->voter->vote($token, $asset, [AssetVoter::VIEW]);
        static::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testVoteOnAttributeWithNonMatchingHash(): void
    {
        $asset = new Asset();
        $asset->setData('test-data-content');

        $request = new Request(['hash' => 'wrong-hash']);
        $this->requestStack->push($request);

        $token = $this->createMock(TokenInterface::class);

        $result = $this->voter->vote($token, $asset, [AssetVoter::VIEW]);
        static::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testVoteOnAttributeWithMissingHashParameter(): void
    {
        $asset = new Asset();
        $asset->setData('test-data-content');

        $request = new Request(); // No hash parameter
        $this->requestStack->push($request);

        $token = $this->createMock(TokenInterface::class);

        $result = $this->voter->vote($token, $asset, [AssetVoter::VIEW]);
        static::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    #[TestWith(['image data 1', 'image data 2'])]
    #[TestWith(['short', 'longer data content'])]
    #[TestWith(['', 'non-empty'])]
    public function testHashComparisonWithDifferentData(string $assetData, string $otherData): void
    {
        $asset = new Asset();
        $asset->setData($assetData);

        $otherAsset = new Asset();
        $otherAsset->setData($otherData);
        $wrongHash = $otherAsset->getHash();

        $request = new Request(['hash' => $wrongHash]);
        $this->requestStack->push($request);

        $token = $this->createMock(TokenInterface::class);

        $result = $this->voter->vote($token, $asset, [AssetVoter::VIEW]);
        static::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }
}
