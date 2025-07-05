import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SubmitButtonFormWidget } from './submit-button-form-widget';

describe('SubmitButtonFormWidget', () => {
  let component: SubmitButtonFormWidget;
  let fixture: ComponentFixture<SubmitButtonFormWidget>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SubmitButtonFormWidget]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SubmitButtonFormWidget);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
