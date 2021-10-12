<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Integration\Service\Config;

use DR\GitCommitNotification\Exception\ConfigException;
use DR\GitCommitNotification\Service\Config\ConfigLoader;
use DR\GitCommitNotification\Tests\AbstractKernelTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @coversNothing
 */
class ConfigLoaderTest extends AbstractKernelTest
{
    /** @var MockObject|InputInterface */
    private InputInterface $input;
    private ConfigLoader   $configLoader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->input = $this->createMock(InputInterface::class);
        /** @var ConfigLoader $configLoader */
        $configLoader       = static::getContainer()->get(ConfigLoader::class);
        $this->configLoader = $configLoader;
    }

    /**
     * @throws ConfigException
     */
    public function testLoadSuccess(): void
    {
        $configPath = $this->getFilePath('valid-config.xml');

        // setup mocks
        $this->input->expects(static::exactly(2))->method('getOption')->with('config')->willReturn($configPath->getPathname());

        // load config
        $config = $this->configLoader->load($this->input);

        // assert repository
        static::assertCount(1, $config->repositories->getRepositories());
        $repository = $config->repositories->getRepositories()[0];
        static::assertSame('sherlock-repository', $repository->name);
        static::assertSame('https://gitlab.example.com/sherlock/holmes.git', $repository->url);
        static::assertSame('upsource', $repository->upsourceProjectId);
        static::assertSame(5, $repository->gitlabProjectId);

        // assert rule
        $rule = $config->getRules()[0];
        static::assertSame('Example rules', $rule->name);
        static::assertCount(1, $rule->repositories->getRepositories());
        static::assertSame('sherlock-repository', $rule->repositories->getRepositories()[0]->name);
        static::assertSame('upsource', $rule->theme);
        static::assertSame('once-per-hour', $rule->frequency);
        static::assertCount(1, $rule->recipients->getRecipients());
        static::assertNotNull($rule->externalLinks);
        static::assertNull($rule->include);
        static::assertNotNull($rule->exclude);
        static::assertFalse($rule->excludeMergeCommits);
        static::assertTrue($rule->ignoreAllSpace);
        static::assertTrue($rule->ignoreSpaceChange);
        static::assertFalse($rule->ignoreSpaceAtEol);
        static::assertTrue($rule->ignoreBlankLines);
        static::assertSame('patience', $rule->diffAlgorithm);

        // assert external links
        $links = $rule->externalLinks->getExternalLinks();
        static::assertCount(2, $links);
        static::assertSame('B#{}', $links[0]->pattern);
        static::assertSame('https://example.com/entity/id/{}', $links[1]->url);

        // assert recipients
        $recipient = $rule->recipients->getRecipients()[0];
        static::assertSame('Sherlock Holmes', $recipient->name);
        static::assertSame('sherlock@example.com', $recipient->email);

        // assert definition
        $exclusion = $rule->exclude;
        static::assertSame('subject', $exclusion->getSubjects()[0]);
        static::assertSame('file', $exclusion->getFiles()[0]);
        static::assertSame('author', $exclusion->getAuthors()[0]);
    }

    /**
     * @throws ConfigException
     */
    public function testLoadFailure(): void
    {
        $configPath = $this->getFilePath('invalid-config.xml');

        // setup mocks
        $this->input->expects(static::exactly(2))->method('getOption')->with('config')->willReturn($configPath->getPathname());

        $this->expectException(ConfigException::class);
        $this->configLoader->load($this->input);
    }
}
