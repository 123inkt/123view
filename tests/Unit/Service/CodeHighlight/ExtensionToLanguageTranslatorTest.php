<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeHighlight;

use DR\GitCommitNotification\Service\CodeHighlight\ExtensionToLanguageTranslator;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeHighlight\ExtensionToLanguageTranslator
 */
class ExtensionToLanguageTranslatorTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     * @covers ::translate
     */
    public function testTranslate(string $extension, ?string $expectedLanguage): void
    {
        $translator     = new ExtensionToLanguageTranslator();
        $actualLanguage = $translator->translate($extension);
        static::assertSame($expectedLanguage, $actualLanguage);
    }

    /**
     * @return array<string, array<string|null>>
     */
    public function dataProvider(): array
    {
        return [
            "css"      => ["css", "css"],
            "env"      => ["env", "ini"],
            "js"       => ["js", "javascript"],
            "json"     => ["json", "json"],
            "json5"    => ["json5", "json"],
            "htaccess" => ["htaccess", "apache"],
            "less"     => ["less", "less"],
            "md"       => ["md", "markdown"],
            "php"      => ["php", "php"],
            "py"       => ["py", "python"],
            "scss"     => ["scss", "scss"],
            "sass"     => ["sass", "scss"],
            "sh"       => ["sh", "bash"],
            "sql"      => ["sql", "sql"],
            "ts"       => ["ts", "typescript"],
            "twig"     => ["twig", "twig"],
            "html"     => ["html", "xml"],
            "xml"      => ["xml", "xml"],
            "yaml"     => ["yaml", "yaml"],
            "yml"      => ["yml", "yaml"],
            "foobar"   => ["foobar", null]
        ];
    }
}
