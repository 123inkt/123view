<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Config\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class AbstractController extends SymfonyAbstractController
{
    public function getUser(): User
    {
        $user = parent::getUser();
        if ($user === null || $user instanceof User === false) {
            throw new AccessDeniedException('Access denied');
        }

        return $user;
    }
}
