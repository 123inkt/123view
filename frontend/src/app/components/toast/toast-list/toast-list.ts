import {AsyncPipe} from '@angular/common';
import {Component} from '@angular/core';
import {ToastMessage} from '@component/toast/toast-message/toast-message';
import {ToastService} from '@service/toast-service';

@Component({
    selector: 'app-toast-list',
    imports: [AsyncPipe, ToastMessage],
    templateUrl: './toast-list.html',
    styleUrl: './toast-list.scss'
})
export class ToastList {
    constructor(public readonly toastService: ToastService) {
    }
}
