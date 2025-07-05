import {Component, input} from '@angular/core';
import {Attributes} from '@directive/attributes';
import FormView from '@model/FormView';

@Component({
  selector: 'password-form-widget',
  imports: [Attributes],
  templateUrl: './password-form-widget.html'
})
export class PasswordFormWidget {
  public form = input.required<FormView>();
}
