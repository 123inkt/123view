import {Injectable} from '@angular/core';
import Toast from '@model/Toast';
import {BehaviorSubject} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ToastService {
    public readonly toasts$;
    private readonly toastsSubject;
    private toasts: Toast[] = [];

    constructor() {
        this.toastsSubject = new BehaviorSubject<Toast[]>([]);
        this.toasts$       = this.toastsSubject.asObservable();
        this.showSuccess('success');
        this.showError('error');
        this.showInfo('info');
    }

    public showSuccess(message: string): void {
        this.showMessage(message, 'success');
    }

    public showError(message: string): void {
        this.showMessage(message, 'error');
    }

    public showInfo(message: string): void {
        this.showMessage(message, 'info');
    }

    private showMessage(message: string, type: 'success' | 'error' | 'info'): void {
        const toast = new Toast(
            message,
            type,
            (toast) => {
                this.toasts = this.toasts.filter(t => t !== toast);
                this.toastsSubject.next(this.toasts);
            }
        );
        this.toasts.push(toast);
        this.toastsSubject.next(this.toasts);
    }
}
