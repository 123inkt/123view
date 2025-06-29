import {Routes} from '@angular/router';
import {Home} from './home/home';
import {Login} from './login/login';

export const routes: Routes = [
  {path: 'login', component: Login, data:{requiresLogin: false}},
  {path: '', component: Home, data:{requiresLogin: true}}
];
