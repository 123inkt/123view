<?php
declare(strict_types=1);

use DR\Review\Doctrine\Type\MailThemeType;
use DR\Review\Entity\Notification\Recipient;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\User\User;
use DR\Review\Security\Role\Roles;
use League\Uri\Uri;

$name  = 'Sherlock Holmes';
$email = 'sherlock@example.com';

return [
    (new Rule())
        ->setUser(
            (new User())
                ->setId(123)
                ->setName($name)
                ->setEmail($email)
                ->setRoles([Roles::ROLE_USER])
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
                ->setUrl(Uri::new('https://example.com/detectives/sherlock.git'))
        )
];
