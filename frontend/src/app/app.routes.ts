import {Routes} from '@angular/router';
import {Home} from './component/home/home';
import {Login} from './component/login/login';
import {authenticationGuard} from './guard/authentication-guard';

export const routes: Routes = [
  {path: 'login', component: Login, data: {requiresLogin: false}, canActivate: [authenticationGuard]},
  {path: '', component: Home, data: {requiresLogin: true}, canActivate: [authenticationGuard]}
];
