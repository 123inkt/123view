import {Routes} from '@angular/router';
import {Login} from '@component/login/login';
import {RepositoriesPage} from '@component/repositories/repositories-page';
import {ReviewList} from '@component/review-list/review-list';
import {environment} from '@environment/environment';
import {authenticationGuard} from '@guard/authentication-guard';
import {LoginViewModelResolver} from '@resolver/login-view-model-resolver';
import {ReviewListActivitiesViewModelResolver} from '@resolver/review-list-activities-view-model-resolver.service';
import {ReviewListViewModelResolver} from '@resolver/review-list-view-model-resolver.service';
import {ActivitiesViewModelResolver} from '@resolver/activities-view-model-resolver.service';
import {RepositoriesViewModelResolver} from '@resolver/repositories-view-model-resolver.service';

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
        component: RepositoriesPage,
        data: {requiresLogin: true},
        title: environment.appName + ' - ' + $localize`Projects`,
        resolve: {projectsViewModel: RepositoriesViewModelResolver, timelineViewModel: ActivitiesViewModelResolver},
        canActivate: [authenticationGuard]
    },
    {
        path: 'app/projects/:id/reviews',
        component: ReviewList,
        data: {requiresLogin: true},
        title: environment.appName + ' - ' + $localize`Reviews`,
        resolve: {reviewsViewModel: ReviewListViewModelResolver, timelineViewModel: ReviewListActivitiesViewModelResolver},
        canActivate: [authenticationGuard]
    }
];
