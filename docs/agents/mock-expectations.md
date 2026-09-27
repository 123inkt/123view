# PHPUnit Mock Expectations

## `AllowMockObjectsWithoutExpectations`

Do not use `#[AllowMockObjectsWithoutExpectations]` in tests. Do not add the corresponding `use` statement as a workaround.

This attribute hides PHPUnit notices for mocks that have no configured expectations. Those notices identify collaborators whose interaction with the system under test is not documented by the test.

## Expectations Per Code Path

Configure every mock used by a test method:

- If the collaborator must be called, use an explicit invocation count such as `once()` or `exactly()`, and specify arguments and return values when relevant.
- If the collaborator must not be called, add a `never()` expectation for the whole mock:

```php
$this->service->expects($this->never())->method(static::anything());
```

- Tests that only exercise `supports()` methods must mark every injected collaborator as `never()` because the handler should not call services while checking support.
- A bare `method(...)->willReturn(...)` stub does not replace an invocation expectation. When the method is part of the executed path, configure it through `expects($this->once())` or the appropriate count.

Keep expectations specific to the branch being tested. Do not configure a collaborator as optional merely to silence PHPUnit.

## Verification

After changing these tests:

1. Run PHPUnit for the affected test files or methods, not the full suite by default.
2. Confirm the run has no PHPUnit notices about mocks without expectations.
3. Run PHPCS for the changed test files.
4. Confirm no test PHP files import or apply `AllowMockObjectsWithoutExpectations`.
