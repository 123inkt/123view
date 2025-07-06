import {Injectable} from '@angular/core';
import {AbstractControl, ValidationErrors, Validators} from '@angular/forms';
import FormView from '@model/FormView';

@Injectable({
  providedIn: 'root'
})
export class FormService {
  public static readonly Types: string[] = [
    'email',
    'text',
    'number',
    'search',
    'password',
    'hidden',
    'submit'
  ];

  public createFromControl(formView: FormView): boolean {
    return this.getFormType(formView) !== 'submit';
  }

  public getFormType(formView: FormView): string {
    for (const block of formView.vars.block_prefixes.reverse()) {
      if (FormService.Types.includes(block)) {
        return block;
      }
    }
    throw new Error(`Unknown form type for FormView: ${formView.vars.full_name}`);
  }

  public createValidators(formView: FormView): ((control: AbstractControl<any, any>) => ValidationErrors | null)[] {
    const validators = [];
    // add required validator
    if (formView.vars.required === true) {
      validators.push(Validators.required);
    }
    // add email validator
    if (this.getFormType(formView) === 'email') {
      validators.push(Validators.email);
    }
    // add pattern validator
    if (formView.vars.attr['pattern'] !== undefined) {
      validators.push(Validators.pattern(String(formView.vars.attr['pattern'])));
    }
    // add min length validator
    if (formView.vars.attr['minlength'] !== undefined) {
      validators.push(Validators.minLength(Number(formView.vars.attr['minlength'])));
    }
    // add max length validator
    if (formView.vars.attr['maxlength'] !== undefined) {
      validators.push(Validators.maxLength(Number(formView.vars.attr['maxlength'])));
    }
    return validators;
  }
}
