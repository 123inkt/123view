<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Config;

use DR\GitCommitNotification\Entity\Config\Configuration;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Exception\ConfigException;
use DR\GitCommitNotification\Service\Config\ConfigLoader;
use DR\GitCommitNotification\Service\Config\ConfigPathResolver;
use DR\GitCommitNotification\Service\Config\ConfigValidator;
use DR\GitCommitNotification\Tests\AbstractTest;
use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use SplFileInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Config\ConfigLoader
 * @covers ::__construct
 */
class ConfigLoaderTest extends AbstractTest
{
    /** @var ConfigPathResolver|MockObject */
    private ConfigPathResolver $resolver;
    /** @var ConfigValidator|MockObject */
    private ConfigValidator $validator;
    /** @var SerializerInterface|MockObject */
    private SerializerInterface $serializer;
    /** @var InputInterface|MockObject */
    private InputInterface $input;

    private ConfigLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();
        vfsStream::setup('ConfigLoaderTest');
        $this->input      = $this->createMock(InputInterface::class);
        $this->resolver   = $this->createMock(ConfigPathResolver::class);
        $this->validator  = $this->createMock(ConfigValidator::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->loader     = new ConfigLoader($this->log, $this->resolver, $this->validator, $this->serializer);
    }

    /**
     * @covers ::load
     * @throws Exception
     */
    public function testLoadWithValidateException(): void
    {
        $file = new SplFileInfo(vfsStream::url('ConfigLoaderTest/validate-exception.xml'));
        file_put_contents($file->getPathname(), 'foobar');

        // setup mocks
        $this->resolver->expects(static::once())->method('resolve')->with($this->input)->willReturn($file);
        $this->validator->expects(static::once())
            ->method('validate')
            ->with('foobar')
            ->willThrowException(new ConfigException('Failed to validate'));

        // set expectations
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Failed to validate');
        $this->loader->load('once-per-hour', $this->input);
    }

    /**
     * @covers ::load
     * @throws Exception
     */
    public function testLoadSuccess(): void
    {
        $file = new SplFileInfo(vfsStream::url('ConfigLoaderTest/validate.xml'));
        file_put_contents($file->getPathname(), 'foobar');

        // setup data model
        $rule       = new Rule();
        $rule->name = "Foobar";
        $config     = new Configuration();
        $config->addRule($rule);

        // setup mocks
        $this->log->expects(static::exactly(4))->method('info');
        $this->resolver->expects(static::once())->method('resolve')->with($this->input)->willReturn($file);
        $this->validator->expects(static::once())->method('validate')->with('foobar');
        $this->serializer->expects(static::once())
            ->method('deserialize')
            ->with('foobar', Configuration::class, XmlEncoder::FORMAT)
            ->willReturn($config);

        // set expectations
        $actual = $this->loader->load('once-per-hour', $this->input);
        static::assertSame($config, $actual);
        static::assertSame(3600, $config->endTime->getTimestamp() - $config->startTime->getTimestamp());
    }
}
