<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository;

use DR\Review\Controller\App\Admin\Credentials\CredentialsController;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Form\Repository\Property\GitlabProjectIdType;
use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\Transformer\RepositoryUrlTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Repository>
 */
class RepositoryType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RepositoryCredentialRepository $credentialRepository,
        private readonly string $gitlabApiUrl
    ) {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('active', CheckboxType::class, ['label' => 'active', 'required' => false]);
        $builder->add('favorite', CheckboxType::class, ['label' => 'favorite', 'required' => false]);
        $builder->add(
            'name',
            TextType::class,
            ['label' => 'name', 'help' => 'repository.name.help', 'required' => true, 'attr' => ['maxlength' => 255]]
        );
        $builder->add('displayName', TextType::class, ['label' => 'display.name', 'required' => true, 'attr' => ['maxlength' => 255]]);
        $builder->add('mainBranchName', TextType::class, ['label' => 'main.branch', 'required' => true, 'attr' => ['maxlength' => 255]]);
        $builder->add(
            'url',
            UrlType::class,
            ['label' => 'url', 'required' => true, 'attr' => ['maxlength' => 255], 'constraints' => new Assert\Url(requireTld: true)]
        );
        $builder->add(
            'credential',
            ChoiceType::class,
            [
                'label'                       => 'credentials',
                'required'                    => false,
                'choice_translation_domain'   => false,
                'help'                        => 'repository.credentials.help',
                'help_translation_parameters' => ['url' => $this->urlGenerator->generate(CredentialsController::class)],
                'choices'                     => $this->credentialRepository->findBy([], ['name' => 'ASC']),
                'choice_value'                => static fn(?RepositoryCredential $credential) => $credential?->getId(),
                'choice_label'                => static fn(?RepositoryCredential $credential) => $credential?->getName(),
            ]
        );
        $builder->add(
            'gitType',
            ChoiceType::class,
            [
                'label'    => 'git.type',
                'choices'  => [
                    'git.type.gitlab' => RepositoryGitType::GITLAB,
                    'git.type.github' => RepositoryGitType::GITHUB,
                    'git.type.other'  => '',
                ],
                'setter'   => [$this, 'setGitType'],
                'expanded' => false,
                'multiple' => false,
            ]
        );
        $builder->add(
            'updateRevisionsInterval',
            IntegerType::class,
            ['label' => 'update.revisions.interval', 'help' => 'repository.update.interval.help', 'required' => true, 'attr' => ['min' => 0]]
        );
        $builder->add(
            'validateRevisionsInterval',
            IntegerType::class,
            ['label' => 'validate.revisions.interval', 'help' => 'repository.validation.interval.help', 'required' => true, 'attr' => ['min' => 0]]
        );

        if ($this->gitlabApiUrl !== '') {
            $builder->add('gitlabProjectId', GitlabProjectIdType::class);
            $builder->add(
                'gitApprovalSync',
                CheckboxType::class,
                ['required' => false, 'label' => 'git.approval.sync.label.checkbox']
            );
        }

        $builder->get('url')->addModelTransformer(new RepositoryUrlTransformer());
    }

    public function setGitType(Repository $repository, string $value): void
    {
        /** @var RepositoryGitType::GITLAB|RepositoryGitType::GITHUB|null $value */
        $value = $value === '' ? null : $value;
        $repository->setGitType($value);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Repository::class]);
    }
}
