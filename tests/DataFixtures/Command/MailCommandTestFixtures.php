<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures\Command;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Doctrine\Type\MailThemeType;
use DR\Review\Entity\Config\ExternalLink;
use DR\Review\Entity\Notification\Recipient;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\User\User;
use DR\Review\Security\Role\Roles;
use League\Uri\Uri;

class MailCommandTestFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user       = $this->createUser();
        $repository = $this->createRepository();

        $manager->persist($user);
        $manager->persist($repository);
        $manager->persist($this->createRule($user, $repository));
        $manager->persist($this->createExternalLink());
        $manager->flush();
    }

    private function createUser(): User
    {
        return (new User())
            ->setId(123)
            ->setName('Sherlock Holmes')
            ->setEmail('sherlock@example.com')
            ->setRoles([Roles::ROLE_USER]);
    }

    private function createRepository(): Repository
    {
        return (new Repository())
            ->setName('sherlock')
            ->setDisplayName('sherlock')
            ->setUrl(Uri::new('https://example.com/detectives/sherlock.git'));
    }

    private function createExternalLink(): ExternalLink
    {
        return (new ExternalLink())
            ->setPattern('B#{}')
            ->setUrl('https://example.com/detectives/issue/{}');
    }

    private function createRule(User $user, Repository $repository): Rule
    {
        return (new Rule())
            ->setUser($user)
            ->setName('Detectives')
            ->setActive(true)
            ->setRuleOptions(
                (new RuleOptions())
                    ->setSubject('My commits')
                    ->setTheme(MailThemeType::DARCULA)
            )
            ->addRecipient(
                (new Recipient())
                    ->setEmail('sherlock@example.com')
                    ->setName('Sherlock Holmes')
            )
            ->addRepository($repository);
    }
}
