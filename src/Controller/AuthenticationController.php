<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Recipient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController extends AbstractController
{
    #[Route('/', self::class)]
    public function __invoke(ManagerRegistry $doctrine): Response
    {
        $recipient = $doctrine->getRepository(Recipient::class)->find(1);
        if ($recipient === null) {
            throw new NotFoundHttpException('Recipient not found: ' . 1);
        }

        return new Response('hello world:' . $recipient->getName() . '(' . $recipient->getEmail() . ')');
    }
}
