import { TestBed } from '@angular/core/testing';

import { FormGroupService } from './form-group-service';

describe('FormGroupService', () => {
  let service: FormGroupService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(FormGroupService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
