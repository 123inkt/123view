import {Routes} from '@angular/router';
import {Home} from '@component/home/home';
import {Login} from '@component/login/login';
import {environment} from '@environment/environment';
import {authenticationGuard} from '@guard/authentication-guard';
import {LoginViewModelResolver} from '@resolver/login-view-model-resolver';

export const routes: Routes = [
  {
    path: 'login',
    component: Login,
    data: {requiresLogin: false},
    title: environment.appName + ' - Login',
    resolve: {resolvedData: LoginViewModelResolver},
    canActivate: [authenticationGuard]
  },
  {path: '', component: Home, data: {requiresLogin: true}, title: environment.appName, canActivate: [authenticationGuard]}
];
