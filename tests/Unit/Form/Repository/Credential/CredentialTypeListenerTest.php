<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository\Credential;

use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Form\Repository\Credential\BasicAuthCredentialType;
use DR\Review\Form\Repository\Credential\CredentialTypeListener;
use DR\Review\Form\Repository\Credential\SshKeyCredentialType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

#[CoversClass(CredentialTypeListener::class)]
class CredentialTypeListenerTest extends AbstractTestCase
{
    private CredentialTypeListener $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new CredentialTypeListener();
    }

    public function testInvokeWithRepositoryCredentialSshKey(): void
    {
        $credential = (new RepositoryCredential())->setAuthType(AuthenticationType::SSH_KEY);
        $form       = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('add')
            ->with('credentials', SshKeyCredentialType::class, ['label' => false]);

        ($this->listener)(new FormEvent($form, $credential));
    }

    public function testInvokeWithRepositoryCredentialBasicAuth(): void
    {
        $credential = (new RepositoryCredential())->setAuthType(AuthenticationType::BASIC_AUTH);
        $form       = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('add')
            ->with('credentials', BasicAuthCredentialType::class, ['label' => false]);

        ($this->listener)(new FormEvent($form, $credential));
    }

    public function testInvokeWithArraySshKey(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('add')
            ->with('credentials', SshKeyCredentialType::class, ['label' => false]);

        ($this->listener)(new FormEvent($form, ['authType' => AuthenticationType::SSH_KEY]));
    }

    public function testInvokeWithArrayMissingAuthTypeDefaultsToBasicAuth(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('add')
            ->with('credentials', BasicAuthCredentialType::class, ['label' => false]);

        ($this->listener)(new FormEvent($form, []));
    }

    public function testInvokeWithArrayNonStringAuthTypeDefaultsToBasicAuth(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('add')
            ->with('credentials', BasicAuthCredentialType::class, ['label' => false]);

        ($this->listener)(new FormEvent($form, ['authType' => 42]));
    }

    public function testInvokeWithNullDefaultsToBasicAuth(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('add')
            ->with('credentials', BasicAuthCredentialType::class, ['label' => false]);

        ($this->listener)(new FormEvent($form, null));
    }
}
