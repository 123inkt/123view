<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Anthropic;

use DR\Review\Service\Api\Anthropic\AnthropicResponseParser;
use DR\Review\Tests\AbstractTestCase;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AnthropicResponseParser::class)]
class AnthropicResponseParserTest extends AbstractTestCase
{
    public function testParse(): void
    {
        $response = Assert::string(file_get_contents(__DIR__. '/response.txt'));
        $parser = new AnthropicResponseParser();
        $parser->parse($response);
    }
}
