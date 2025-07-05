import { Component, input } from '@angular/core';
import FormView from '@model/FormView';

@Component({
  selector: 'form-widget',
  imports: [],
  templateUrl: './form-widget.html'
})
export class FormWidget {
   public form = input.required<FormView>();
}
