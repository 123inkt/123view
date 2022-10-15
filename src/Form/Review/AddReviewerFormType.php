<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use Doctrine\ORM\EntityRepository;
use DR\GitCommitNotification\Entity\Config\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class AddReviewerFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private Security $security)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentUser = $this->security->getUser();

        //$builder->setAction($this->urlGenerator->generate(RuleController::class, ['id' => $data['rule']?->getId()]));
        $builder->setMethod('POST');

        $builder->add('users', EntityType::class, [
            'label'             => '',
            'class'             => User::class,
            'choice_label'      => 'name',
            'choice_value'      => static fn(?User $entity) => $entity ? $entity->getId() : '',
            'query_builder'     => static fn(EntityRepository $er) => $er->createQueryBuilder('u')->orderBy('u.name', 'ASC'),
            'preferred_choices' => [$currentUser],
            'multiple'          => false,
            'expanded'          => false,
        ]);
    }
}
