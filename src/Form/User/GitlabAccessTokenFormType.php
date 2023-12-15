<?php
declare(strict_types=1);

namespace DR\Review\Form\User;

use DR\Review\Controller\App\User\UserGitIntegrationController;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\User\GitAccessToken;
use DR\Review\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GitlabAccessTokenFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setAction($this->urlGenerator->generate(UserGitIntegrationController::class));
        $builder->setMethod('POST');
        $builder->add(
            'user',
            TextType::class,
            [
                'label' => false,
                'setter' => [$this, 'setToken'],
                'getter' => [$this, 'getToken']
            ]
        );
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }

    public function setToken(User $user, string $value): void
    {
        $token = $user->getGitAccessTokens()->findFirst(static fn(GitAccessToken $token) => $token->getGitType() === RepositoryGitType::GITLAB);

        if ($token === null && $value !== '') {
            $token = new GitAccessToken();
            $token->setGitType(RepositoryGitType::GITLAB);
            $token->setToken($value);
            $token->setUser($user);
            $user->getGitAccessTokens()->add($token);
        } elseif ($token !== null) {
            if ($value === '') {
                $user->getGitAccessTokens()->removeElement($token);
            } else {
                $token->setToken($value);
            }
        }
    }

    public function getToken(User $user): string
    {
        $token = $user->getGitAccessTokens()->findFirst(static fn(GitAccessToken $token) => $token->getGitType() === RepositoryGitType::GITLAB);

        return $token?->getToken() ?? '';
    }
}
