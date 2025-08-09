<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeHighlight;

use DR\Review\Service\CodeHighlight\FilenameToLanguageTranslator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FilenameToLanguageTranslator::class)]
class FilenameToLanguageTranslatorTest extends AbstractTestCase
{
    #[DataProvider('dataProvider')]
    public function testTranslate(string $filename, ?string $expectedLanguage): void
    {
        $translator     = new FilenameToLanguageTranslator();
        $actualLanguage = $translator->translate($filename);
        static::assertSame($expectedLanguage, $actualLanguage);
    }

    /**
     * @return array<string, array<string|null>>
     */
    public static function dataProvider(): array
    {
        return [
            "dockerfile"  => ["/path/to/Dockerfile", "dockerfile"],
            "css"         => ["/path/to/file.css", "css"],
            "env"         => ["/path/to/file.env", "ini"],
            "ini"         => ["/path/to/file.ini", "ini"],
            "js"          => ["/path/to/file.js", "javascript"],
            "json"        => ["/path/to/file.json", "json"],
            "json5"       => ["/path/to/file.json5", "json"],
            "htaccess"    => ["/path/to/.htaccess", "apache"],
            "less"        => ["/path/to/file.less", "less"],
            "md"          => ["/path/to/file.md", "markdown"],
            "php"         => ["/path/to/file.php", "php"],
            "py"          => ["/path/to/file.py", "python"],
            "scss"        => ["/path/to/file.scss", "scss"],
            "sass"        => ["/path/to/file.sass", "scss"],
            "sh"          => ["/path/to/file.sh", "bash"],
            "sql"         => ["/path/to/file.sql", "sql"],
            "ts"          => ["/path/to/file.ts", "typescript"],
            "twig"        => ["/path/to/file.twig", "twig"],
            "html"        => ["/path/to/file.html", "xml"],
            "xml"         => ["/path/to/file.xml", "xml"],
            "xml.dist"    => ["/path/to/file.xml.dist", "xml"],
            "xml.local"   => ["/path/to/file.xml.local", "xml"],
            "xml.example" => ["/path/to/file.xml.example", "xml"],
            "xlf"         => ["/path/to/file.xlf", "xml"],
            "yaml"        => ["/path/to/file.yaml", "yaml"],
            "yml"         => ["/path/to/file.yml", "yaml"],
            "foobar"      => ["/path/to/file.foobar", null]
        ];
    }
}
