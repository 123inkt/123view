<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception;

use DR\Review\Exception\XMLException;
use DR\Review\Tests\AbstractTestCase;
use LibXMLError;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(XMLException::class)]
class XMLExceptionTest extends AbstractTestCase
{
    public function testGetters(): void
    {
        $error          = new LibXMLError();
        $error->code    = 123;
        $error->message = 'message';
        $error->file    = 'file';
        $error->line    = 456;

        $exception = new XMLException($error);
        static::assertSame(123, $exception->getCode());
        static::assertSame('message', $exception->getMessage());
        static::assertSame('file', $exception->getXmlFile());
        static::assertSame(456, $exception->getXmlLine());
    }
}
