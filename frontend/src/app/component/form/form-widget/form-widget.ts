import {NgComponentOutlet} from '@angular/common';
import {Component, input, Type} from '@angular/core';
import {EmailFormWidget} from '@component/form/email-form-widget/email-form-widget';
import {HiddenFormWidget} from '@component/form/hidden-form-widget/hidden-form-widget';
import {PasswordFormWidget} from '@component/form/password-form-widget/password-form-widget';
import FormView from '@model/FormView';

@Component({
  selector: 'form-widget',
  imports: [NgComponentOutlet],
  template: '<ng-container *ngComponentOutlet="getComponent(); inputs: {form: form()}" />'
})
export class FormWidget {
  public form = input.required<FormView>();

  private componentMap: Record<string, Type<unknown>> = {
    email_widget: EmailFormWidget,
    password_widget: PasswordFormWidget,
    hidden_widget: HiddenFormWidget
  };

  public getComponent(): Type<unknown> {
    for (const prefix of this.form().block_prefixes.reverse()) {
      const component = this.componentMap[prefix + '_widget'];
      if (component) {
        console.log(`Using component for prefix "${prefix}":`, component);
        return component;
      }
    }
    throw new Error(`No component found for input type "${this.form().full_name}"`);
  }
}
