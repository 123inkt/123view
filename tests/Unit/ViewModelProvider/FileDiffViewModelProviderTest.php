<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModelProvider;

use DR\GitCommitNotification\ViewModelProvider\FileDiffViewModelProvider;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModelProvider\FileDiffViewModelProvider
 * @covers ::__construct
 */
class FileDiffViewModelProviderTest extends AbstractTestCase
{
    /**
     * @covers ::getFileDiffViewModel
     */
    public function testGetFileDiffViewModel(): void
    {
    }
}
