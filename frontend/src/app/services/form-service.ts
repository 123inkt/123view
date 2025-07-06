import {Injectable} from '@angular/core';
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
}
