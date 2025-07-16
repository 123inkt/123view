import {HttpInterceptorFn} from '@angular/common/http';
import {inject} from '@angular/core';
import {Router} from '@angular/router';
import {environment} from '@environment/environment';
import {ltrim} from '@lib/Strings';
import {AuthenticationService} from '@service/auth/authentication-service';
import {TokenStore} from '@service/auth/token-store';
import HttpClientContext from '@service/http-client-context';
import {catchError, switchMap, throwError} from 'rxjs';

export const httpclientUrlInterceptor: HttpInterceptorFn = (req, next) => {
    // regular http request, ignore
    if (req.context.get(HttpClientContext.BackendApi).valueOf() === false) {
        return next(req);
    }

    // prepend backend url and port to request url
    const apiReq = req.clone(
        {
            url: `//${window.location.hostname}:${environment.apiPort}/${ltrim(req.url, '/')}`,
            headers: req.headers.set('Accept', 'application/json'),
            withCredentials: true
        }
    );

    // jwt token about expire within 5 minutes, refresh it
    if (req.context.get(HttpClientContext.PublicUrl).valueOf() === false && inject(TokenStore).willExpire()) {
        return inject(AuthenticationService).refresh()
            .pipe(
                // chain api request after refresh token request
                switchMap(() => next(apiReq)),
                // on error, redirect to login page
                catchError((error) => {
                    inject(Router).navigate(['login']);
                    return throwError(() => error);
                })
            );
    }

    return next(apiReq);
};
