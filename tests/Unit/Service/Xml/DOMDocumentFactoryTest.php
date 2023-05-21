<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Xml;

use DR\Review\Exception\ParseException;
use DR\Review\Exception\XMLException;
use DR\Review\Service\Xml\DOMDocumentFactory;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DOMDocumentFactory::class)]
class DOMDocumentFactoryTest extends AbstractTestCase
{
    private DOMDocumentFactory $documentFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->documentFactory = new DOMDocumentFactory();
    }

    /**
     * @throws XMLException|ParseException
     */
    public function testCreateFromStringFailure(): void
    {
        $this->expectException(XMLException::class);
        $this->documentFactory->createFromString('<xml>');
    }

    /**
     * @throws XMLException|ParseException
     */
    public function testCreateFromStringValid(): void
    {
        $xml      = Assert::isString(file_get_contents(__DIR__ . '/../../../../phpunit.xml.dist'));
        $document = $this->documentFactory->createFromString($xml);

        static::assertSame(1, $document->childNodes->length);
    }
}
