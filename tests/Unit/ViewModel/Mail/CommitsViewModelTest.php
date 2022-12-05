<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\Mail;

use DR\Review\Entity\Git\Author;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\Mail\CommitsViewModel;

/**
 * @coversDefaultClass \DR\Review\ViewModel\Mail\CommitsViewModel
 * @covers ::__construct
 */
class CommitsViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::getAuthors
     */
    public function testGetAuthors(): void
    {
        $commitA = $this->createCommit(new Author('Sherlock', 'sherlock@example.com'));
        $commitB = $this->createCommit(new Author('Sherlock', 'sherlock@example.com'));
        $commitC = $this->createCommit(new Author('Watson', 'watson@example.com'));

        $model = new CommitsViewModel([$commitA, $commitB, $commitC], 'theme');
        static::assertSame(['Sherlock', 'Watson'], array_values($model->getAuthors()));
    }
}
