<?php
declare(strict_types=1);

namespace DR\Review;

use DR\Utils\Assert;
use Locale;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

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

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->parameters()->set('.container.dumper.inline_factories', true);

        $container->import('../config/{packages}/*.php');
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.php');
        $container->import('../config/{services}.php');
        $container->import('../config/{services}/' . $this->environment . '/*.php');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.php');
        $routes->import('../config/{routes}/*.php');
    }
}
