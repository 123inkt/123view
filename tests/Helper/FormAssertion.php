<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use function PHPUnit\Framework\atLeastOnce;

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
        $this->form->expects(atLeastOnce())->method('handleRequest')->with($request)->willReturnSelf();

        return $this;
    }

    public function isValidWillReturn(bool $value): self
    {
        $this->form->expects(atLeastOnce())->method('isValid')->willReturn($value);

        return $this;
    }

    public function isSubmittedWillReturn(bool $value): self
    {
        $this->form->expects(atLeastOnce())->method('isSubmitted')->willReturn($value);

        return $this;
    }

    public function createViewWillReturn(FormView $formView): void
    {
        $this->form->expects(atLeastOnce())->method('createView')->willReturn($formView);
    }
}
