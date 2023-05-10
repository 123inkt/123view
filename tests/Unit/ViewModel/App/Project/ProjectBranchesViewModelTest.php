<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Project;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Project\ProjectBranchesViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectBranchesViewModel::class)]
class ProjectBranchesViewModelTest extends AbstractTestCase
{
    public function testIsMerged(): void
    {
        $repository = new Repository();
        $model      = new ProjectBranchesViewModel($repository, ['branch A', 'branch B'], ['branch B']);

        static::assertFalse($model->isMerged('branch A'));
        static::assertTrue($model->isMerged('branch B'));
    }
}
