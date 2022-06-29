<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FormAssertion
{
    /**
     * @param FormInterface&MockObject $form
     */
    public function __construct(private FormInterface $form)
    {
    }

    public function handleRequest(Request $request): self
    {
        $this->form->method('handleRequest')->with($request)->willReturnSelf();

        return $this;
    }

    public function isValidWillReturn($value): self
    {
        $this->form->method('isValid')->willReturn($value);

        return $this;
    }

    public function isSubmittedWillReturn($value): self
    {
        $this->form->method('isSubmitted')->willReturn($value);

        return $this;
    }
}
