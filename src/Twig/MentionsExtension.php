<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use DR\GitCommitNotification\Utility\Arrays;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MentionsExtension extends AbstractExtension
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [new TwigFilter('mentions', [$this, 'convert'])];
    }

    public function convert(string $string): string
    {
        $matchCount = preg_match_all('/@user:(\d+)\[.*?]/', $string, $matches);
        if ($matchCount === 0) {
            return $string;
        }

        // get all user ids
        $userIds = array_map('intval', array_unique($matches[1]));
        $users   = $this->userRepository->findBy(['id' => $userIds]);

        // replace @mention with name + email
        for ($i = 0; $i < $matchCount; $i++) {
            $userId = (int)$matches[1][$i];
            $user   = Arrays::tryFind($users, static fn(User $user) => $user->getId() === $userId);
            if ($user === null) {
                continue;
            }

            $markdown = sprintf('[@%s](mailto:%s)', $user->getName(), $user->getEmail());
            $string   = str_replace($matches[0][$i], $markdown, $string);
        }

        return $string;
    }
}
