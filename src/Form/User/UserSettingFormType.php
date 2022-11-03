<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\User;

use DR\GitCommitNotification\Controller\App\User\UserSettingController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserSettingFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setAction($this->urlGenerator->generate(UserSettingController::class));
        $builder->setMethod('POST');
        $builder->add('setting', UserSettingType::class, ['label' => false]);
        $builder->add('save', SubmitType::class, ['label' => 'Save']);
    }
}
