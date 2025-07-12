import {Component, effect, forwardRef, Inject, input, OnInit} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {Attributes} from '@directive/attributes';
import {EmptyFunction} from '@lib/Functions';
import FormView from '@model/FormView';
import {FormService} from '@service/form-service';

@Component({
    selector: 'form-widget',
    imports: [Attributes],
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            useExisting: forwardRef(() => FormWidget),
            multi: true
        }
    ],
    templateUrl: './form-widget.html'
})
export class FormWidget implements OnInit, ControlValueAccessor {
    public form                            = input.required<FormView>();
    public disabled                        = input<boolean | null>(null);
    public loading                         = input<boolean>(false);
    public formType: string                = 'text';
    public onInput: (event: Event) => void = EmptyFunction;
    public onBlur: () => void              = EmptyFunction;

    constructor(@Inject(FormService) private readonly formService: FormService) {
        effect(() => {
            // when input changes, update FormView
            this.form().vars.disabled = this.disabled() ?? this.form().vars.disabled;
        });
    }

    public ngOnInit(): void {
        this.formType = this.formService.getFormType(this.form());
    }

    public writeValue(obj: any): void {
        this.form().vars.value = String(obj);
    }

    public registerOnChange(fn: any): void {
        this.onInput = (event: Event) => fn((event.target as HTMLInputElement).value);
    }

    public registerOnTouched(fn: any): void {
        this.onBlur = fn;
    }

    public setDisabledState?(isDisabled: boolean): void {
        this.form().vars.disabled = isDisabled;
    }
}
