import {Component, input} from '@angular/core';
import Toast from '@model/Toast';

@Component({
    selector: 'app-toast-message',
    imports: [],
    templateUrl: './toast-message.html',
    styleUrl: './toast-message.scss',
    host: {
        '(click)': 'close()',
        '(animationend)': 'close()'
    }
})
export class ToastMessage {
    public readonly toast = input.required<Toast>();

    public close(): void {
        this.toast().close();
    }
}
