import {HttpEvent, HttpHandler, HttpInterceptor, HttpRequest} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import {Progress} from '@service/progress';
import {finalize, Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export default class ProgressIndicatorHttpInterceptor implements HttpInterceptor {
    constructor(@Inject(Progress) private readonly progress: Progress) {
    }

    public intercept(req: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
        this.progress.setLoading(true);
        return next.handle(req).pipe(finalize(() => this.progress.setLoading(false)));
    }
}
