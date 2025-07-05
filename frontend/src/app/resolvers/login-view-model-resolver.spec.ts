import { TestBed } from '@angular/core/testing';

import { LoginViewModelResolver } from './login-view-model-resolver';

describe('LoginViewModelResolver', () => {
  let resolver: LoginViewModelResolver;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    resolver = TestBed.inject(LoginViewModelResolver);
  });

  it('should be created', () => {
    expect(resolver).toBeTruthy();
  });
});
