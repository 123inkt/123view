import {Component, input} from '@angular/core';
import {Attributes} from '@directive/attributes';
import FormView from '@model/FormView';

@Component({
    selector: 'app-form-label',
    imports: [Attributes],
    templateUrl: './form-label.html'
})
export class FormLabel {
    public form = input.required<FormView>();
}
