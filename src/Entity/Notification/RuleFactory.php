<?php
declare(strict_types=1);

namespace DR\Review\Entity\Notification;

use DR\Review\Doctrine\Type\FilterType;
use DR\Review\Entity\User\User;

class RuleFactory
{
    public static function createDefault(User $user): Rule
    {
        return (new Rule())
            ->setUser($user)
            ->setActive(true)
            ->setRuleOptions(new RuleOptions())
            ->addRecipient((new Recipient())->setEmail($user->getEmail())->setName($user->getName() ?? ''))
            ->addFilter((new Filter())->setInclusion(false)->setType(FilterType::AUTHOR)->setPattern($user->getEmail()));
    }
}
