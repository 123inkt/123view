import {HttpEvent, HttpHandler, HttpInterceptor, HttpRequest} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import {Router} from '@angular/router';
import {environment} from '@environment/environment';
import {ltrim} from '@lib/Strings';
import {AuthenticationService} from '@service/auth/authentication-service';
import {TokenStore} from '@service/auth/token-store';
import HttpClientContext from '@service/http-client-context';
import {catchError, Observable, switchMap, throwError} from 'rxjs';

@Injectable({providedIn: 'root'})
export default class BackendApiHttpInterceptor implements HttpInterceptor {
    constructor(
        @Inject(AuthenticationService) private readonly authenticationService: AuthenticationService,
        @Inject(TokenStore) private readonly tokenStore: TokenStore,
        @Inject(Router) private readonly router: Router) {
    }

    public intercept(req: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
        // regular http request, ignore
        if (req.context.get(HttpClientContext.BackendApi).valueOf() === false) {
            return next.handle(req);
        }

        // prepend backend url and port to request url
        const apiReq = req.clone(
            {
                url: `//${window.location.hostname}:${environment.apiPort}/${ltrim(req.url, '/')}`,
                headers: req.headers.set('Accept', 'application/json'),
                withCredentials: true
            }
        );

        // jwt token is about expire within 5 minutes, refresh it
        if (req.context.get(HttpClientContext.PublicUrl).valueOf() === false && this.tokenStore.willExpire()) {
            return this.authenticationService.refresh()
                .pipe(
                    // on error, redirect to login page
                    catchError((error) => {
                        this.router.navigate(['login']);
                        return throwError(() => error);
                    }),
                    // chain api request after refresh token request
                    switchMap(() => {
                        console.info('token refresh, invoking api req: ' + apiReq.url);
                        return next.handle(apiReq)
                    })
                );
        }

        return next.handle(apiReq);
    }
}
