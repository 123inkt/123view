import {Routes} from '@angular/router';
import {Login} from '@component/login/login';
import {Projects} from '@component/projects/projects';
import {environment} from '@environment/environment';
import {authenticationGuard} from '@guard/authentication-guard';
import {LoginViewModelResolver} from '@resolver/login-view-model-resolver';
import {ProjectsTimelineViewModelResolver} from '@resolver/projects-timeline-view-model-resolver';
import {ProjectsViewModelResolver} from '@resolver/projects-view-model-resolver';

export const routes: Routes = [
  {
    path: 'login',
    component: Login,
    data: {requiresLogin: false},
    title: environment.appName + ' - ' + $localize`Login`,
    resolve: {loginViewModel: LoginViewModelResolver},
    canActivate: [authenticationGuard]
  },
  {
    path: '',
    component: Projects,
    data: {requiresLogin: true},
    title: environment.appName + ' - ' + $localize`Projects`,
    resolve: {projectsViewModel: ProjectsViewModelResolver, timelineViewModel: ProjectsTimelineViewModelResolver},
    canActivate: [authenticationGuard]
  }
];
