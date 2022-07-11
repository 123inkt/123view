<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Mail;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Service\Mail\MailSubjectFormatter;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Mail\MailSubjectFormatter
 * @covers ::__construct
 */
class MailSubjectFormatterTest extends AbstractTestCase
{
    /**
     * @covers ::format
     */
    public function testFormatAllVariables(): void
    {
        $rule = new Rule();
        $rule->setName('name');

        $repository         = (new Repository())->setName('repository');
        $commit             = $this->createCommit(new Author('Sherlock', 'sherlock@example.com'));
        $commit->repository = $repository;

        $subject = '{name} {authors} {repositories}';

        $result = (new MailSubjectFormatter())->format($subject, $rule, [$commit]);
        static::assertsame('name Sherlock repository', $result);
    }

    /**
     * @covers ::format
     */
    public function testFormatEmptyVariables(): void
    {
        $rule = new Rule();
        $subject = '#{name}#{authors}#{repositories}#';

        $result = (new MailSubjectFormatter())->format($subject, $rule, []);
        static::assertsame('####', $result);
    }
}
