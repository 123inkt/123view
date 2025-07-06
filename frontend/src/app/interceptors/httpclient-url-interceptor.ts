import { HttpInterceptorFn } from '@angular/common/http';
import {environment} from '@environment/environment';

export const httpclientUrlInterceptor: HttpInterceptorFn = (req, next) => {
  return next(req.clone({ url: `//${window.location.hostname}:${environment.apiPort}/${req.url}`, withCredentials: true }));
};
