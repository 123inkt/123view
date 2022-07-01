<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig\InlineCss;

use DOMDocument;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\InlineCss\CssToInlineStyles;
use Yep\Reflection\ReflectionClass;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\InlineCss\CssToInlineStyles
 */
class CssToInlineStylesTest extends AbstractTestCase
{
    /**
     * @covers ::createDomDocumentFromHtml
     */
    public function testCreateDomDocumentFromHtml(): void
    {
        $html = '<html><body></body></html>';

        $reflection = ReflectionClass::from(new CssToInlineStyles());
        /** @var DOMDocument $document */
        $document = $reflection->invokeMethod('createDomDocumentFromHtml', [$html]);

        static::assertFalse($document->formatOutput);
        static::assertTrue($document->preserveWhiteSpace);
    }
}
