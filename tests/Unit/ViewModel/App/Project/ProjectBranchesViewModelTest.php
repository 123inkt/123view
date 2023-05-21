<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Project;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Project\ProjectBranchesViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectBranchesViewModel::class)]
class ProjectBranchesViewModelTest extends AbstractTestCase
{
    public function testGetReview(): void
    {
        $review     = new CodeReview();
        $repository = new Repository();
        $model      = new ProjectBranchesViewModel($repository, [], [], ['branch' => $review]);

        static::assertNull($model->getReview('foobar'));
        static::assertSame($review, $model->getReview('branch'));
    }

    public function testIsMerged(): void
    {
        $repository = new Repository();
        $model      = new ProjectBranchesViewModel($repository, ['branch A', 'branch B'], ['branch B'], []);

        static::assertFalse($model->isMerged('branch A'));
        static::assertTrue($model->isMerged('branch B'));
    }
}
