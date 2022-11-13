<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Request;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\Constraint\RequestConstraint;
use DigitalRevolution\SymfonyRequestValidation\Constraint\RequestConstraintFactory;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
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
    protected AbstractValidatedRequest            $validatedRequest;
    protected ValidatorInterface&MockObject       $validator;
    protected RequestConstraintFactory&MockObject $constraintFactory;
    protected Request                             $request;

    public function setUp(): void
    {
        parent::setUp();
        $this->request = new Request();
        $stack         = new RequestStack();
        $stack->push($this->request);
        $this->validator         = $this->createMock(ValidatorInterface::class);
        $this->constraintFactory = $this->createMock(RequestConstraintFactory::class);
        $this->validatedRequest  = new (static::getClassToTest())($stack, $this->validator, $this->constraintFactory);
    }

    protected function expectGetValidationRules(?ValidationRules $rules): void
    {
        $constraint    = $this->createMock(RequestConstraint::class);
        $violationList = new ConstraintViolationList();

        $this->constraintFactory
            ->expects(self::atLeastOnce())
            ->method('createConstraint')
            ->with($rules)
            ->willReturn($constraint);
        $this->validator
            ->expects(self::atLeastOnce())
            ->method('validate')
            ->with(self::isInstanceOf(Request::class), $constraint)
            ->willReturn($violationList);
    }

    /**
     * @return class-string<T>
     */
    abstract protected static function getClassToTest(): string;
}
