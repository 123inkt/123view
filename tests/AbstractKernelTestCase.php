<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractKernelTestCase extends KernelTestCase
{
    use TestTrait;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel(['environment' => 'test', 'debug' => false]);
    }
}
