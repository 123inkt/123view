<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewerStateResolver::class)]
class CodeReviewerStateResolverTest extends AbstractTestCase
{
    private CodeReviewerStateResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new CodeReviewerStateResolver();
    }

    public function testIsAccepted(): void
    {
        $review = new CodeReview();
        static::assertSame(CodeReviewerStateType::OPEN, $this->resolver->getReviewersState($review));

        $reviewer = new CodeReviewer();
        $reviewer->setUser((new User())->setEmail('email'));
        $review->getReviewers()->add($reviewer);
        static::assertSame(CodeReviewerStateType::OPEN, $this->resolver->getReviewersState($review));

        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        static::assertSame(CodeReviewerStateType::ACCEPTED, $this->resolver->getReviewersState($review));
    }

    public function testIsAcceptedSelfAccepted(): void
    {
        $review   = new CodeReview();
        $revision = (new Revision())->setAuthorEmail('author1');
        $review->getRevisions()->add($revision);

        $reviewer = new CodeReviewer();
        $reviewer->setUser((new User())->setEmail('author1'));
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $review->getReviewers()->add($reviewer);

        static::assertSame(CodeReviewerStateType::OPEN, $this->resolver->getReviewersState($review));
    }

    public function testIsAcceptedSelfAcceptedMultiReviewers(): void
    {
        $review   = new CodeReview();
        $revisionA = (new Revision())->setAuthorEmail('author1');
        $revisionB = (new Revision())->setAuthorEmail('author2');
        $review->getRevisions()->add($revisionA);
        $review->getRevisions()->add($revisionB);

        $reviewerA = new CodeReviewer();
        $reviewerA->setUser((new User())->setEmail('author1'));
        $reviewerA->setState(CodeReviewerStateType::ACCEPTED);
        $review->getReviewers()->add($reviewerA);

        $reviewerB = new CodeReviewer();
        $reviewerB->setUser((new User())->setEmail('author2'));
        $reviewerB->setState(CodeReviewerStateType::ACCEPTED);
        $review->getReviewers()->add($reviewerB);

        static::assertSame(CodeReviewerStateType::ACCEPTED, $this->resolver->getReviewersState($review));
    }

    public function testIsAcceptedButNotSelfAccepted(): void
    {
        $review   = new CodeReview();
        $revision = (new Revision())->setAuthorEmail('author1');
        $review->getRevisions()->add($revision);

        $reviewer = new CodeReviewer();
        $reviewer->setUser((new User())->setEmail('author1'));
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $review->getReviewers()->add($reviewer);

        $reviewer = new CodeReviewer();
        $reviewer->setUser((new User())->setEmail('author2'));
        $reviewer->setState(CodeReviewerStateType::ACCEPTED);
        $review->getReviewers()->add($reviewer);

        static::assertSame(CodeReviewerStateType::ACCEPTED, $this->resolver->getReviewersState($review));
    }

    public function testIsRejected(): void
    {
        $review = new CodeReview();
        static::assertSame(CodeReviewerStateType::OPEN, $this->resolver->getReviewersState($review));

        $reviewer = new CodeReviewer();
        $reviewer->setUser((new User())->setEmail('email'));
        $review->getReviewers()->add($reviewer);
        static::assertSame(CodeReviewerStateType::OPEN, $this->resolver->getReviewersState($review));

        $reviewer->setState(CodeReviewerStateType::REJECTED);
        static::assertSame(CodeReviewerStateType::REJECTED, $this->resolver->getReviewersState($review));
    }
}
