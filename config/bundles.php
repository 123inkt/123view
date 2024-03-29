<?php

declare(strict_types=1);

return [
    ApiPlatform\Symfony\Bundle\ApiPlatformBundle::class                              => ['all' => true],
    DR\SymfonyTraceBundle\SymfonyTraceBundle::class                                  => ['all' => true],
    DigitalRevolution\SymfonyRequestValidation\Bundle\RequestValidationBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class                             => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class                 => ['all' => true],
    FD\LogViewer\FDLogViewerBundle::class                                            => ['all' => true],
    Liip\MonitorBundle\LiipMonitorBundle::class                                      => ['all' => true],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class                            => ['all' => true],
    Symfony\Bundle\MercureBundle\MercureBundle::class                                => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class                                => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class                              => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class                                      => ['all' => true],
    Symfony\UX\StimulusBundle\StimulusBundle::class                                  => ['all' => true],
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class                           => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class                                => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class                                    => ['dev' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class                     => ['dev' => true, 'test' => true],
    Liip\TestFixturesBundle\LiipTestFixturesBundle::class                            => ['dev' => true, 'test' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class                                    => ['dev' => true, 'test' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class                        => ['dev' => true, 'test' => true],
];
