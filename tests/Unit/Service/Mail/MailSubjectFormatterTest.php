<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Mail;

use DR\Review\Entity\Git\Author;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Mail\MailSubjectFormatter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MailSubjectFormatter::class)]
class MailSubjectFormatterTest extends AbstractTestCase
{
    public function testFormatAllVariables(): void
    {
        $rule = new Rule();
        $rule->setName('name');

        $repository         = (new Repository())->setName('repository');
        $commit             = $this->createCommit(new Author('Sherlock', 'sherlock@example.com'));
        $commit->repository = $repository;

        $subject = '{name} {authors} {repositories}';

        $result = (new MailSubjectFormatter())->format($subject, $rule, [$commit]);
        static::assertSame('name Sherlock repository', $result);
    }

    public function testFormatEmptyVariables(): void
    {
        $rule    = (new Rule())->setName('');
        $subject = '#{name}#{authors}#{repositories}#';

        $result = (new MailSubjectFormatter())->format($subject, $rule, []);
        static::assertSame('####', $result);
    }
}
