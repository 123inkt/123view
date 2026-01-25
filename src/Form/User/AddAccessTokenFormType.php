<?php
declare(strict_types=1);

namespace DR\Review\Form\User;

use DR\Review\Controller\App\User\UserAccessTokenController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Length;

class AddAccessTokenFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setAction($this->urlGenerator->generate(UserAccessTokenController::class));
        $builder->setMethod(Request::METHOD_POST);
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => false,
                'attr' => ['maxlength' => 100, 'autocomplete' => 'off', 'placeholder' => 'name'],
                'constraints' => [new Length(max: 100)]
            ]
        );
        $builder->add('create', SubmitType::class, ['label' => 'create.token']);
    }
}
