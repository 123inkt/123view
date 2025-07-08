import {inject} from '@angular/core';
import {CanActivateFn, Router, UrlTree} from '@angular/router';
import {TokenStore} from '@service/token-store';

export const authenticationGuard: CanActivateFn = (route, state): UrlTree | boolean => {
  if (route.data['requiresLogin'] && inject(TokenStore).isFullyAuthenticated() === false) {
    return inject(Router).createUrlTree(['/login'], { queryParams: { returnUrl: state.url } });
  }
  return true;
};
