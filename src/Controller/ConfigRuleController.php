<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\Type\FilterType;
use DR\GitCommitNotification\Doctrine\Type\FrequencyType;
use DR\GitCommitNotification\Entity\Config\Definition;
use DR\GitCommitNotification\Entity\ExternalLink;
use DR\GitCommitNotification\Entity\Filter;
use DR\GitCommitNotification\Entity\Recipient;
use DR\GitCommitNotification\Entity\Repository;
use DR\GitCommitNotification\Entity\RepositoryProperty;
use DR\GitCommitNotification\Entity\Rule;
use DR\GitCommitNotification\Entity\RuleOptions;
use DR\GitCommitNotification\Entity\User;
use DR\GitCommitNotification\Service\Config\ConfigLoader;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @SuppressWarnings(PHPMD)
 */
class ConfigRuleController extends AbstractController
{
    private function handleDefinition(Rule $rule, Definition $definition, bool $include): void
    {
        foreach ($definition->getAuthors() as $author) {
            $filter = new Filter();
            $filter->setInclusion($include);
            $filter->setPattern($author);
            $filter->setType(FilterType::AUTHOR);
            $rule->addFilter($filter);
        }

        foreach ($definition->getSubjects() as $subject) {
            $filter = new Filter();
            $filter->setInclusion($include);
            $filter->setPattern($subject);
            $filter->setType(FilterType::SUBJECT);
            $rule->addFilter($filter);
        }

        foreach ($definition->getFiles() as $file) {
            $filter = new Filter();
            $filter->setInclusion($include);
            $filter->setPattern($file);
            $filter->setType(FilterType::FILE);
            $rule->addFilter($filter);
        }
    }

    /**
     * @throws Exception
     */
    #[Route('/load-configs', self::class)]
    public function __invoke(ManagerRegistry $doctrine, ConfigLoader $loader): Response
    {
        $em                   = $doctrine->getManager();
        $repositoryRepository = $em->getRepository(Repository::class);
        $userRepository       = $em->getRepository(User::class);
        $elRepository         = $em->getRepository(ExternalLink::class);
        $config               = $loader->load(FrequencyType::ONCE_PER_HOUR, new ArrayInput([]));

        foreach ($config->repositories->getRepositories() as $repository) {
            $dbRepository = new Repository();
            $dbRepository->setName($repository->name);
            $dbRepository->setUrl($repository->url);

            if ($repository->gitlabProjectId !== null) {
                $property = new RepositoryProperty();
                $property->setName('gitlab-project-id');
                $property->setValue((string)$repository->gitlabProjectId);
                $dbRepository->addRepositoryProperty($property);
            }

            if ($repository->upsourceProjectId !== null) {
                $property = new RepositoryProperty();
                $property->setName('upsource-project-id');
                $property->setValue($repository->upsourceProjectId);
                $dbRepository->addRepositoryProperty($property);
            }

            $em->persist($dbRepository);
        }

        $em->flush();

        foreach ($config->getRules() as $rule) {
            $newRule = new Rule();
            $newRule->setActive($rule->active);
            $newRule->setName($rule->name);

            $options = new RuleOptions();
            $options->setSubject($rule->subject);
            $options->setDiffAlgorithm($rule->diffAlgorithm);
            $options->setFrequency($rule->frequency);
            $options->setTheme($rule->theme);
            $options->setIgnoreAllSpace($rule->ignoreAllSpace);
            $options->setIgnoreBlankLines($rule->ignoreBlankLines);
            $options->setIgnoreSpaceChange($rule->ignoreSpaceChange);
            $options->setIgnoreSpaceAtEol($rule->ignoreSpaceAtEol);
            $newRule->setRuleOptions($options);

            if ($rule->externalLinks !== null) {
                foreach ($rule->externalLinks->getExternalLinks() as $link) {
                    $currentLink = $elRepository->findOneBy(['pattern' => $link->pattern]);
                    if ($currentLink !== null) {
                        continue;
                    }

                    $currentLink = new ExternalLink();
                    $currentLink->setPattern($link->pattern);
                    $currentLink->setUrl($link->url);
                    $em->persist($currentLink);
                    $em->flush();
                }
            }

            foreach ($rule->recipients->getRecipients() as $recipient) {
                $user = $userRepository->findOneBy(['email' => $recipient->email]);
                if ($user === null) {
                    $user = new User();
                    $user->setEmail($recipient->email);
                    $user->setName($recipient->name ?? '');
                    $em->persist($user);
                }
                $newRule->setUser($user);

                $r = new Recipient();
                $r->setName($recipient->name);
                $r->setEmail($recipient->email);
                $newRule->addRecipient($r);
            }

            foreach ($rule->repositories->getRepositories() as $repoRef) {
                $repository = $repositoryRepository->findOneBy(['name' => $repoRef->name]);
                if ($repository === null) {
                    throw new RuntimeException('Unknown repository: ' . $repoRef->name);
                }
                $newRule->addRepository($repository);
            }

            if ($rule->include !== null) {
                $this->handleDefinition($newRule, $rule->include, true);
            }

            if ($rule->exclude !== null) {
                $this->handleDefinition($newRule, $rule->exclude, false);
            }
            $em->persist($newRule);
            $em->flush();
        }

        return new JsonResponse('done');
    }
}
