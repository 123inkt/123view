import {Component, input} from '@angular/core';
import {Attributes} from '@directive/attributes';
import FormView from '@model/FormView';

@Component({
  selector: 'hidden-form-widget',
  imports: [Attributes],
  templateUrl: './hidden-form-widget.html'
})
export class HiddenFormWidget {
  public form = input.required<FormView>();
}
