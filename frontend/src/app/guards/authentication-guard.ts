import {inject} from '@angular/core';
import {CanActivateFn, Router, UrlTree} from '@angular/router';
import {AuthenticationService} from '@service/authentication-service';

export const authenticationGuard: CanActivateFn = (route, state): UrlTree | boolean => {
  if (route.data['requiresLogin'] && inject(AuthenticationService).isAuthenticated() === false) {
    return inject(Router).createUrlTree(['/login'], { queryParams: { returnUrl: state.url } });
  }
  return true;
};
