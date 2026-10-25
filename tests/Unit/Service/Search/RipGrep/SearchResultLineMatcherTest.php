<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep;

use DR\Review\Service\Search\RipGrep\SearchResultLineMatcher;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SearchResultLineMatcher::class)]
class SearchResultLineMatcherTest extends AbstractTestCase
{
    private SearchResultLineMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcher = new SearchResultLineMatcher();
    }

    public function testMatchesBeginWithoutPattern(): void
    {
        static::assertTrue($this->matcher->matches($this->createEntry('begin', 'src/file.php'), null));
    }

    public function testDoesNotMatchNonBeginEntry(): void
    {
        static::assertFalse($this->matcher->matches($this->createEntry('match', 'src/file.php'), null));
    }

    public function testMatchesFilenamePattern(): void
    {
        static::assertTrue($this->matcher->matches($this->createEntry('begin', 'src/composer.json'), 'composer\\.json'));
    }

    public function testDoesNotMatchWrongPattern(): void
    {
        static::assertFalse($this->matcher->matches($this->createEntry('begin', 'src/composer.lock'), 'composer\\.json'));
    }

    public function testMatchesPatternWithPathSeparator(): void
    {
        static::assertTrue($this->matcher->matches($this->createEntry('begin', 'src/Service/file.php'), 'src/Service/'));
    }

    /**
     * @param 'begin'|'context'|'match'|'end' $type
     * @return array{type: 'begin'|'context'|'match'|'end', data: array{path: array{text: string}, lines: array{text: string}, line_number: int}}
     */
    private function createEntry(string $type, string $path): array
    {
        return [
            'type' => $type,
            'data' => [
                'path' => ['text' => $path],
                'lines' => ['text' => ''],
                'line_number' => 1,
            ],
        ];
    }
}
