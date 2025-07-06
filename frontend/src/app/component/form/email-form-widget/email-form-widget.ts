import {Component, input} from '@angular/core';
import {ControlValueAccessor} from '@angular/forms';
import {Attributes} from '@directive/attributes';
import FormView from '@model/FormView';

@Component({
  selector: 'email-form-widget',
  imports: [Attributes],
  templateUrl: './email-form-widget.html'
})
export class EmailFormWidget implements ControlValueAccessor {
  public form = input.required<FormView>();
  public onChange = (_event: Event) => {};

  public writeValue(obj: any): void {
    this.form().vars.value = String(obj);
  }

  public registerOnChange(fn: any): void {
    this.onChange = (event: Event) => fn((event.target as HTMLInputElement).value);
  }

  public registerOnTouched(): void {
    // not implemented
  }

  public setDisabledState?(isDisabled: boolean): void {
    this.form().vars.disabled = isDisabled;
  }
}
