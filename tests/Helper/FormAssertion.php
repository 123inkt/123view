<?php
declare(strict_types=1);

namespace DR\Review\Tests\Helper;

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use function PHPUnit\Framework\atLeastOnce;

class FormAssertion
{
    /**
     * @param FormInterface&MockObject $form
     */
    public function __construct(private readonly FormInterface $form, private readonly TestCase $testCase)
    {
    }

    public function handleRequest(Request $request): self
    {
        $this->form->expects(atLeastOnce())->method('handleRequest')->with($request)->willReturnSelf();

        return $this;
    }

    /**
     * @param array<string, int|string|float|null> $keyValueData
     */
    public function getWillReturn(array $keyValueData): self
    {
        $this->form->expects(atLeastOnce())
            ->method('get')
            ->willReturnCallback(
                function ($key) use ($keyValueData) {
                    if (array_key_exists($key, $keyValueData) === false) {
                        throw new RuntimeException('Missing key in data: ' . $key);
                    }

                    $mock = (new MockBuilder($this->testCase, FormInterface::class))->getMock();
                    $mock->method('getData')->willReturn($keyValueData[$key]);

                    return $mock;
                }
            );

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

    public function getDataWillReturn(mixed $data): self
    {
        $this->form->expects(atLeastOnce())->method('getData')->willReturn($data);

        return $this;
    }

    public function createViewWillReturn(FormView $formView): void
    {
        $this->form->expects(atLeastOnce())->method('createView')->willReturn($formView);
    }
}
