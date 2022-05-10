<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController extends AbstractController
{
    #[Route('/', self::class)]
    public function __invoke(): Response
    {
        return new Response('hello world');
    }
}
