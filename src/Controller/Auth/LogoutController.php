<?php
declare(strict_types=1);

namespace DR\Review\Controller\Auth;

use RuntimeException;
use Symfony\Component\Routing\Attribute\Route;

class LogoutController
{
    #[Route('logout', self::class, methods: 'GET')]
    public function __invoke(): void
    {
        // never called, handled by security.php
        throw new RuntimeException('Logout security route is not configured');
    }
}
