import {HttpClient, HttpInterceptorFn} from '@angular/common/http';
import {inject} from '@angular/core';
import {environment} from '@environment/environment';
import {ltrim} from '@lib/Strings';
import HttpClientContext from '@service/http-client-context';
import {TokenStore} from '@service/token-store';

export const httpclientUrlInterceptor: HttpInterceptorFn = (req, next) => {
    // TODO find better way to handle. Maybe separate HttpClient for API requests?
    if (/\/i18n\/en\.json/.test(req.url)) {
        return next(req);
    }

    const apiReq = req.clone(
        {
            url: `//${window.location.hostname}:${environment.apiPort}/${ltrim(req.url, '/')}`,
            headers: req.headers.set('Accept', 'application/json'),
            withCredentials: true
        }
    );

    // jwt token about expire within 5 minutes
    if (req.context.get(HttpClientContext.PublicUrl).valueOf() && inject(TokenStore).willExpire()) {
        //inject(HttpClient).get('api/token/refresh');
    }

    // .pipe(switchMap((username) => this.translator.get(translationKey, {username: username, file: filename})));

    return next(apiReq);

    // return of(1)
    //     .pipe(
    //         switchMap((val) => {
    //             console.log(val);
    //             return next(apiReq);
    //         })
    //     );

    // return next(apiReq)
    //     .pipe(
    //         tap((val) => {
    //             console.log('value', val);
    //         }),
    //         share()
    //     );
};
