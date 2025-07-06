import {Inject, Injectable} from '@angular/core';
import {FormControl, FormGroup} from '@angular/forms';
import FormView from '@model/FormView';
import {FormService} from '@service/form-service';

@Injectable({
  providedIn: 'root'
})
export class FormGroupService {

  constructor(@Inject(FormService) private readonly formService: FormService) {
  }

  public createFormGroup<T extends FormView>(formView: T): FormGroup {
    const formGroupValues: { [key: string]: FormGroup | FormControl } = {};

    for (const key in formView) {
      if (formView.hasOwnProperty(key) === false || key === 'vars') {
        continue;
      }

      const value: T = <T>formView[key];
      if (Object.keys(value).length > 1) {
        formGroupValues[key] = this.createFormGroup(value);
      } else if (this.formService.createFromControl(value)) {
        formGroupValues[key] = new FormControl(value.vars.value);
      }
    }

    return new FormGroup(formGroupValues);
  }
}
