<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SaveRuleController extends AbstractController
{
    #[Route('/rules/rule', self::class, methods: 'POST')]
    public function __invoke(): array
    {
        throw new \RuntimeException('Woohoo');
    }
}
