<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Config;

use DR\GitCommitNotification\Exception\ConfigException;
use DR\GitCommitNotification\Service\Config\ConfigValidator;
use DR\GitCommitNotification\Tests\AbstractTest;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Config\ConfigValidator
 * @covers ::__construct
 */
class ConfigValidatorTest extends AbstractTest
{
    private ConfigValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ConfigValidator();
    }

    /**
     * @covers ::validate
     */
    public function testValidateSuccess(): void
    {
        $xml = $this->getFileContents('valid-config.xml');

        try {
            $this->validator->validate($xml);
            $success = true;
        } catch (Throwable $e) {
            $success = false;
        }
        static::assertTrue($success);
    }

    /**
     * @covers ::validate
     */
    public function testValidateInvalidXml(): void
    {
        $xml = $this->getFileContents('invalid-config.xml');

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Error occurred while validating the config xml');
        $this->validator->validate($xml);
    }

    /**
     * @covers ::validate
     * @throws ConfigException
     */
    public function testValidateFailWithLibXmlError(): void
    {
        $xml = '<foobar>';

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Error occurred while validating the config xml');
        $this->validator->validate($xml);
    }
}
