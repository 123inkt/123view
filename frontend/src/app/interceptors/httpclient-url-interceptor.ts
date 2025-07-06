import {HttpInterceptorFn} from '@angular/common/http';
import {environment} from '@environment/environment';
import {ltrim} from '@lib/Strings';

export const httpclientUrlInterceptor: HttpInterceptorFn = (req, next) => {
  const apiReq = req.clone(
    {
      url: `//${window.location.hostname}:${environment.apiPort}/${ltrim(req.url, '/')}`,
      headers: req.headers.set('Accept', 'application/json'),
      withCredentials: true
    }
  );

  return next(apiReq);
};
