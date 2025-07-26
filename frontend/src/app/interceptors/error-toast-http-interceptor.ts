import {HttpErrorResponse, HttpEvent, HttpHandler, HttpInterceptor, HttpRequest} from '@angular/common/http';
import {Injectable, isDevMode} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {ToastService} from '@service/toast-service';
import {catchError, Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export default class ErrorToastHttpInterceptor implements HttpInterceptor {
    constructor(
        private readonly toastService: ToastService,
        private readonly translator: TranslateService
    ) {
    }

    public intercept(req: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
        return next.handle(req)
            .pipe(
                catchError(
                    (response) => {
                        this.handleErrorResponse(response);
                        throw response;
                    }
                )
            );
    }

    private handleErrorResponse(response: HttpErrorResponse): void {
        const messageKey = this.getErrorTranslationKey(response.status);
        this.translator.get(messageKey).subscribe((msg) => this.showToast(msg, response));
    }

    private showToast(message: string, response: HttpErrorResponse): void {
        if (isDevMode()) {
            message += `<br><small>Status: ${response.status}<br>URL: ${response.url}</small>`;
        }

        this.toastService.showError(message);
    }

    private getErrorTranslationKey(status: number | undefined): string {
        if (status === 400) {
            return 'request.error.bad.request';
        }
        if (status === 401) {
            return 'request.error.unauthorized';
        }
        if (status === 403) {
            return 'request.error.forbidden';
        }
        if (status === 404) {
            return 'request.error.not.found';
        }
        return 'request.error.internal.server.error';
    }
}
