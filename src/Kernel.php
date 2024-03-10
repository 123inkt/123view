<?php
declare(strict_types=1);

namespace DR\Review;

use DR\Utils\Assert;
use Locale;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * @codeCoverageIgnore
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();
        date_default_timezone_set(Assert::string($this->getContainer()->getParameter('timezone')));
        Locale::setDefault(Assert::string($this->getContainer()->getParameter('locale')));
    }

    public function getBuildDir(): string
    {
        return $this->getProjectDir() . '/var/build/' . $this->environment;
    }
}
