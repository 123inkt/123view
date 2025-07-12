import {HttpInterceptorFn} from '@angular/common/http';
import {environment} from '@environment/environment';
import {ltrim} from '@lib/Strings';

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

    return next(apiReq);
};
