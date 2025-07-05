import {Component, input} from '@angular/core';
import FormView from '@model/FormView';
import {Attributes} from '@directive/attributes';

@Component({
  selector: 'email-form-widget',
  imports: [Attributes],
  templateUrl: './email-form-widget.html'
})
export class EmailFormWidget {
  public form = input.required<FormView>();
}
