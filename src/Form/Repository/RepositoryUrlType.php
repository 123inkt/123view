<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Transformer\RepositoryUrlTransformer;
use DR\Review\Utility\UriUtil;
use League\Uri\Uri;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RepositoryUrlType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'setter' => static function (Repository $repository, Uri $uri): void {
                    // if no new password, transfer existing password to the new uri
                    [, $password] = UriUtil::credentials($repository->getUrl());
                    [$username, $newPassword] = UriUtil::credentials($uri);
                    $repository->setUrl($uri->withUserInfo($username, $newPassword ?? $password));
                }
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'url',
            UrlType::class,
            [
                'label'       => 'url',
                'required'    => true,
                'attr'        => ['maxlength' => 255],
                'constraints' => new Assert\Url()
            ]
        );
        $builder->add('username', TextType::class, ['label' => 'user.name', 'required' => false, 'attr' => ['maxlength' => 50]]);
        $builder->add('password', PasswordType::class, ['label' => 'password', 'required' => false, 'attr' => ['maxlength' => 50]]);
        $builder->addModelTransformer(new RepositoryUrlTransformer());
    }
}
