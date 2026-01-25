<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository\Credential;

use DR\Review\Controller\App\Admin\Credentials\CredentialController;
use DR\Review\Entity\Repository\RepositoryCredential;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends AbstractType<array{credential: RepositoryCredential|null}>
 */
class EditCredentialFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array{credential: RepositoryCredential|null} $data */
        $data = $options['data'];

        $builder->setAction($this->urlGenerator->generate(CredentialController::class, ['id' => $data['credential']?->getId()]));
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('credential', RepositoryCredentialType::class, ['label' => false]);
        $builder->add('save', SubmitType::class, ['label' => 'save']);
    }
}
