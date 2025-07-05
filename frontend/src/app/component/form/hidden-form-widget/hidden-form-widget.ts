import {Component, input} from '@angular/core';
import FormView from '@model/FormView';

@Component({
  selector: 'hidden-form-widget',
  imports: [],
  templateUrl: './hidden-form-widget.html'
})
export class HiddenFormWidget {
  public form = input.required<FormView>();
}
