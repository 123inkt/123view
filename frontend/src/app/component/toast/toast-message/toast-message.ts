import {Component, input} from '@angular/core';
import Toast from '@model/Toast';

@Component({
    selector: 'app-toast-message',
    imports: [],
    templateUrl: './toast-message.html',
    styleUrl: './toast-message.scss',
    host: {
        '(click)': 'onClick',
        '(animationend)': 'onAnimationEnd()'
    }
})
export class ToastMessage {
    public readonly toast = input.required<Toast>();

    public onClick(): void {
        this.toast().close();
        console.log('toast clicked');
    }

    public onAnimationEnd(): void {
        this.toast().close();
        console.log('Animation ended');
    }
}
