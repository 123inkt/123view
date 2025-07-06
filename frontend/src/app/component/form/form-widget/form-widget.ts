import {Component, forwardRef, Inject, input, OnInit} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {Attributes} from '@directive/attributes';
import FormView from '@model/FormView';
import {FormService} from '@service/form-service';
import {EmptyFunction} from '../../../lib/Functions';

@Component({
  selector: 'form-widget',
  imports: [Attributes],
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => FormWidget),
      multi: true
    }
  ],
  templateUrl: './form-widget.html'
})
export class FormWidget implements OnInit, ControlValueAccessor {
  public form                             = input.required<FormView>();
  public formType: string                 = 'text';
  public onChange: (event: Event) => void = EmptyFunction;
  public onBlur: () => void               = EmptyFunction;

  constructor(@Inject(FormService) private readonly formService: FormService) {
  }

  public ngOnInit(): void {
    this.formType = this.formService.getFormType(this.form());
  }

  public writeValue(obj: any): void {
    this.form().vars.value = String(obj);
  }

  public registerOnChange(fn: any): void {
    this.onChange = (event: Event) => fn((event.target as HTMLInputElement).value);
  }

  public registerOnTouched(fn: any): void {
    this.onBlur = fn;
  }

  public setDisabledState?(isDisabled: boolean): void {
    this.form().vars.disabled = isDisabled;
  }
}
