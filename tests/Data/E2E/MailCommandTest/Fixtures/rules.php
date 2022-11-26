<?php
declare(strict_types=1);

use DR\GitCommitNotification\Doctrine\Type\MailThemeType;
use DR\GitCommitNotification\Entity\Notification\Recipient;
use DR\GitCommitNotification\Entity\Notification\Rule;
use DR\GitCommitNotification\Entity\Notification\RuleOptions;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\User\User;

$name  = 'Sherlock Holmes';
$email = 'sherlock@example.com';

return [
    (new Rule())
        ->setUser(
            (new User())
                ->setName($name)
                ->setEmail($email)
        )
        ->setName('Detectives')
        ->setActive(true)
        ->setRuleOptions(
            (new RuleOptions())
                ->setSubject('My commits')
                ->setTheme(MailThemeType::DARCULA)
        )
        ->addRecipient(
            (new Recipient())
                ->setEmail($email)
                ->setName($name)
        )
        ->addRepository(
            (new Repository())
                ->setName('sherlock')
                ->setUrl('https://example.com/detectives/sherlock.git')
        )
];
