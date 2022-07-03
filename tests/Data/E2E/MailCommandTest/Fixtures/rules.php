<?php
declare(strict_types=1);

use DR\GitCommitNotification\Doctrine\Type\MailThemeType;
use DR\GitCommitNotification\Entity\Config\Recipient;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\RuleOptions;
use DR\GitCommitNotification\Entity\Config\User;

$repository = new Repository();
$repository->setName('sherlock');
$repository->setUrl('https://example.com/detectives/sherlock.git');

$user = new User();
$user->setName('Sherlock Holmes');
$user->setEmail('sherlock@example.com');

$rule = (new Rule())
    ->setUser($user)
    ->setName('Detectives')
    ->setActive(true)
    ->setRuleOptions(new RuleOptions())
    ->addRecipient((new Recipient())->setEmail($user->getEmail() ?? '')->setName($user->getName() ?? ''))
    ->addRepository($repository);
$rule->getRuleOptions()->setSubject('My commits');
$rule->getRuleOptions()->setTheme(MailThemeType::DARCULA);

return [$rule];
