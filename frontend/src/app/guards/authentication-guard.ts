import {inject} from '@angular/core';
import {CanActivateFn, Router, UrlTree} from '@angular/router';

export const authenticationGuard: CanActivateFn = (route, state): UrlTree | boolean => {
  if (route.data['requiresLogin'] /* %% this.loggedIn === false */) {
    return inject(Router).createUrlTree(['/login'], { queryParams: { returnUrl: state.url } });
  }
  return true;
};
