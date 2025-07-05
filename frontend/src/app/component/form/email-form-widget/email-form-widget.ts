import {Component, input} from '@angular/core';
import FormView from '@model/FormView';

@Component({
  selector: 'email-form-widget',
  imports: [],
  templateUrl: './email-form-widget.html'
})
export class EmailFormWidget {
  public form = input.required<FormView>();
}
