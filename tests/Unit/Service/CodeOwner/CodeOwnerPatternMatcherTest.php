<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Review\Service\CodeOwner\CodeOwnerPatternMatcher;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(CodeOwnerPatternMatcher::class)]
class CodeOwnerPatternMatcherTest extends AbstractTestCase
{
    private CodeOwnerPatternMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcher = new CodeOwnerPatternMatcher();
    }

    // Wildcard: * matches any characters within one directory level
    #[TestWith(['*.js', 'foo.js', true])]
    #[TestWith(['*.js', 'src/foo.js', true])]       // unanchored: prefix path is optional
    #[TestWith(['*.js', 'foo.ts', false])]
    // Anchored paths: leading / pins the match to repository root
    #[TestWith(['/src/*.js', 'src/foo.js', true])]
    #[TestWith(['/src/*.js', 'other/src/foo.js', false])]
    // Single * does not cross directory boundaries
    #[TestWith(['src/*.js', 'src/sub/foo.js', false])]
    // Double-star ** crosses multiple directory levels
    #[TestWith(['src/**', 'src/sub/deep/foo.js', true])]
    #[TestWith(['**/*.js', 'src/sub/foo.js', true])]
    // Directory patterns: trailing / matches all files inside recursively
    #[TestWith(['/docs/', 'docs/README.md', true])]
    #[TestWith(['/docs/', 'docs/sub/README.md', true])]
    #[TestWith(['/docs/', 'src/docs/README.md', false])]  // anchored
    // Character class
    #[TestWith(['[abc].js', 'a.js', true])]
    #[TestWith(['[abc].js', 'd.js', false])]
    // Relative paths (no leading /) behave like globstar — match at any depth
    #[TestWith(['README.md', 'README.md', true])]
    #[TestWith(['README.md', 'internal/README.md', true])]
    #[TestWith(['README.md', 'app/lib/README.md', true])]
    #[TestWith(['internal/README.md', 'docs/api/internal/README.md', true])]
    // Globstar /**/ matches zero or more intermediate directory levels
    #[TestWith(['/docs/**/*.md', 'docs/README.md', true])]            // zero levels
    #[TestWith(['/docs/**/*.md', 'docs/api/graphql/README.md', true])] // multiple levels
    #[TestWith(['/docs/**/index.md', 'docs/index.md', true])]         // zero levels (no middle dir)
    #[TestWith(['/docs/**/index.md', 'docs/api/index.md', true])]
    #[TestWith(['/docs/**/index.md', 'docs/api/graphql/index.md', true])]
    // Wildcard path: * matches files in the directory but not in subdirectories
    #[TestWith(['/docs/*.md', 'docs/README.md', true])]
    #[TestWith(['/docs/*.md', 'docs/api/README.md', false])]
    // One-level deep: /docs/*/README.md
    #[TestWith(['/docs/*/README.md', 'docs/api/README.md', true])]
    #[TestWith(['/docs/*/README.md', 'docs/api/graphql/README.md', false])]
    public function testMatch(string $pattern, string $filename, bool $expected): void
    {
        static::assertSame($expected, $this->matcher->match($filename, new OwnerPattern($pattern, [])));
    }
}
