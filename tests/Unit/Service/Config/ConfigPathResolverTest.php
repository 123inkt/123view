<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Config;

use DR\GitCommitNotification\Exception\ConfigException;
use DR\GitCommitNotification\Service\Config\ConfigPathResolver;
use DR\GitCommitNotification\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Config\ConfigPathResolver
 * @covers ::__construct
 */
class ConfigPathResolverTest extends AbstractTest
{
    /** @var MockObject|InputInterface */
    private InputInterface $input;
    /** @var MockObject|Filesystem */
    private Filesystem         $filesystem;
    private ConfigPathResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->input      = $this->createMock(InputInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->resolver   = new ConfigPathResolver($this->filesystem);
    }

    /**
     * @covers ::resolve
     * @throws ConfigException
     */
    public function testResolveInputArgument(): void
    {
        $configPath = '/path/to/config.xml';

        $this->input->expects(static::exactly(2))->method('getOption')->with('config')->willReturn($configPath);
        $this->filesystem->expects(static::once())->method('exists')->with($configPath)->willReturn(true);

        $actual = $this->resolver->resolve($this->input);
        static::assertSame($configPath, $actual->getPathname());
    }

    /**
     * @covers ::resolve
     * @throws ConfigException
     */
    public function testResolveInvalidInputArgumentThrowsException(): void
    {
        $configPath = '/path/to/config.xml';

        $this->input->expects(static::exactly(2))->method('getOption')->with('config')->willReturn($configPath);
        $this->filesystem->expects(static::once())->method('exists')->with($configPath)->willReturn(false);

        $this->expectException(ConfigException::class);
        $this->resolver->resolve($this->input);
    }

    /**
     * @covers ::resolve
     * @throws ConfigException
     */
    public function testResolveProjectConfig(): void
    {
        $configPath = str_replace("\\", "/", dirname(__DIR__, 4) . '/config.xml');

        $this->input->expects(static::once())->method('getOption')->with('config')->willReturn(null);
        $this->filesystem->expects(static::once())->method('exists')->with($configPath)->willReturn(true);

        $actual = $this->resolver->resolve($this->input);
        static::assertSame($configPath, $actual->getPathname());
    }

    /**
     * @covers ::resolve
     * @throws ConfigException
     */
    public function testResolveMissingProjectConfigThrowsException(): void
    {
        $configPath = str_replace("\\", "/", dirname(__DIR__, 4) . '/config.xml');

        $this->input->expects(static::once())->method('getOption')->with('config')->willReturn(null);
        $this->filesystem->expects(static::once())->method('exists')->with($configPath)->willReturn(false);

        $this->expectException(ConfigException::class);
        $this->resolver->resolve($this->input);
    }
}
