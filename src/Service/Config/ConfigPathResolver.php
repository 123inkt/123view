<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Config;

use DR\GitCommitNotification\Exception\ConfigException;
use DR\GitCommitNotification\Utility\Strings;
use SplFileInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ConfigPathResolver
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @throws ConfigException
     */
    public function resolve(InputInterface $input): SplFileInfo
    {
        // resolve config from cli arguments
        if ($input->hasOption('config') && $input->getOption('config') !== null) {
            $configPath = str_replace("\\", "/", Strings::string($input->getOption('config')));

            if ($this->filesystem->exists($configPath) === false) {
                throw new ConfigException(sprintf('Config %s doesn\'t exist.', $configPath));
            }

            return new SplFileInfo($configPath);
        }

        // resolve config in project root
        $configPath = str_replace("\\", "/", dirname(__DIR__, 3) . '/config.xml');

        if ($this->filesystem->exists($configPath) === false) {
            throw new ConfigException(
                'No --config option provided and no config.xml in the root of project. Please provide a config.xml.'
            );
        }

        return new SplFileInfo($configPath);
    }
}
