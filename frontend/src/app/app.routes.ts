import {Routes} from '@angular/router';
import {environment} from '../environments/environment';
import {Home} from './component/home/home';
import {Login} from './component/login/login';
import {authenticationGuard} from './guard/authentication-guard';

export const routes: Routes = [
  {path: 'login', component: Login, data: {requiresLogin: false}, title: environment.appName + ' - Login', canActivate: [authenticationGuard]},
  {path: '', component: Home, data: {requiresLogin: true}, title: environment.appName, canActivate: [authenticationGuard]}
];
