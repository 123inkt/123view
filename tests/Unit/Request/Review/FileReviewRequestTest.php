<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Request\Review\FileReviewRequest;
use DR\Review\Security\SessionKeys;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @extends AbstractRequestTestCase<FileReviewRequest>
 */
#[CoversClass(FileReviewRequest::class)]
class FileReviewRequestTest extends AbstractRequestTestCase
{
    public function testGetFilePath(): void
    {
        $this->request->query->set('filePath', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getFilePath());
    }

    public function testGetComparePolicy(): void
    {
        $session = $this->createMock(Session::class);
        $this->request->setSession($session);

        static::assertSame(DiffComparePolicy::ALL, $this->validatedRequest->getComparisonPolicy());
    }

    public function testGetComparePolicyFromSession(): void
    {
        $session = $this->createMock(Session::class);
        $this->request->setSession($session);

        $session->expects($this->once())
            ->method('get')
            ->with(SessionKeys::DIFF_COMPARISON_POLICY->value)
            ->willReturn(DiffComparePolicy::TRIM->value);

        static::assertSame(DiffComparePolicy::TRIM, $this->validatedRequest->getComparisonPolicy());
    }

    public function testGetDiffMode(): void
    {
        $session = $this->createMock(Session::class);
        $this->request->setSession($session);

        static::assertSame(ReviewDiffModeEnum::INLINE, $this->validatedRequest->getDiffMode());
    }

    public function testGetDiffModeFromSession(): void
    {
        $session = $this->createMock(Session::class);
        $this->request->setSession($session);

        $session->expects($this->once())
            ->method('get')
            ->with(SessionKeys::REVIEW_DIFF_MODE->value)
            ->willReturn(ReviewDiffModeEnum::SIDE_BY_SIDE->value);

        static::assertSame(ReviewDiffModeEnum::SIDE_BY_SIDE, $this->validatedRequest->getDiffMode());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(['query' => ['filePath' => 'required|string|filled']]);
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return FileReviewRequest::class;
    }
}
