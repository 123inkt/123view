import {Component, input} from '@angular/core';
import FormView from '@model/FormView';

@Component({
  selector: 'submit-button-form-widget',
  imports: [],
  templateUrl: './submit-button-form-widget.html'
})
export class SubmitButtonFormWidget {
  public form = input.required<FormView>();
}
