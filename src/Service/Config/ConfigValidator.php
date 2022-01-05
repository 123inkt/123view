<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Config;

use DR\GitCommitNotification\Exception\ConfigException;
use Symfony\Component\Config\Util\XmlUtils;
use Throwable;

class ConfigValidator
{
    private string $configSchema;

    public function __construct()
    {
        $this->configSchema = dirname(__DIR__, 3) . '/config.xsd';
    }

    /**
     * @throws ConfigException
     */
    public function validate(string $xml): void
    {
        try {
            XmlUtils::parse($xml, $this->configSchema);
        } catch (Throwable $e) {
            throw new ConfigException('Error occurred while validating the config xml: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
