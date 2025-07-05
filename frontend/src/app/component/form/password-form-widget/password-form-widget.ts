import {Component, input} from '@angular/core';
import FormView from '@model/FormView';

@Component({
  selector: 'password-form-widget',
  imports: [],
  templateUrl: './password-form-widget.html'
})
export class PasswordFormWidget {
  public form = input.required<FormView>();
}
