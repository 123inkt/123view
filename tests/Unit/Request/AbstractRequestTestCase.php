<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\Constraint\RequestConstraint;
use DigitalRevolution\SymfonyRequestValidation\Constraint\RequestConstraintFactory;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @template T of AbstractValidatedRequest
 */
abstract class AbstractRequestTestCase extends AbstractTestCase
{
    /** @var T */
    protected AbstractValidatedRequest       $validatedRequest;
    protected ValidatorInterface&Stub        $validator;
    protected RequestConstraintFactory&Stub  $constraintFactory;
    protected Request                        $request;

    public function setUp(): void
    {
        parent::setUp();
        $this->request = new Request(content: 'content');
        $stack         = new RequestStack([$this->request]);
        $this->validator         = static::createStub(ValidatorInterface::class);
        $this->constraintFactory = static::createStub(RequestConstraintFactory::class);

        $arguments   = $this->getConstructorArguments();
        $arguments[] = $stack;
        $arguments[] = $this->validator;
        $arguments[] = $this->constraintFactory;

        $className              = static::getClassToTest();
        $this->validatedRequest = new $className(...$arguments);
    }

    protected function expectGetValidationRules(?ValidationRules $rules): void
    {
        // Recreate as mocks so we can set expectations, and rebuild the validated request with fresh mocks
        $this->validator         = $this->createMock(ValidatorInterface::class);
        $this->constraintFactory = $this->createMock(RequestConstraintFactory::class);

        $stack     = new RequestStack([$this->request]);
        $arguments = $this->getConstructorArguments();
        $arguments[] = $stack;
        $arguments[] = $this->validator;
        $arguments[] = $this->constraintFactory;

        $className              = static::getClassToTest();
        $this->validatedRequest = new $className(...$arguments);

        $constraint    = static::createStub(RequestConstraint::class);
        $violationList = new ConstraintViolationList();

        /** @var RequestConstraintFactory&MockObject $constraintFactory */
        $constraintFactory = $this->constraintFactory;
        $constraintFactory
            ->expects($this->atLeastOnce())
            ->method('createConstraint')
            ->with($rules)
            ->willReturn($constraint);

        /** @var ValidatorInterface&MockObject $validator */
        $validator = $this->validator;
        $validator
            ->expects($this->atLeastOnce())
            ->method('validate')
            ->with(self::isInstanceOf(Request::class), $constraint)
            ->willReturn($violationList);
    }

    /**
     * @return class-string<T>
     */
    abstract protected static function getClassToTest(): string;

    /**
     * @return array<int|string|object>
     */
    protected function getConstructorArguments(): array
    {
        return [];
    }
}