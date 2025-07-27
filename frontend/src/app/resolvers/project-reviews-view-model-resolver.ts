import {Inject, Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve} from '@angular/router';
import {toNumber} from '@lib/Numbers';
import {filter} from '@lib/Objects';
import ProjectReviewsViewModel from '@model/viewmodels/ProjectReviewsViewModel';
import {ProjectReviewsService} from '@service/api/project-reviews-service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectReviewsViewModelResolver implements Resolve<ProjectReviewsViewModel> {
    constructor(@Inject(ProjectReviewsService) private readonly reviewsService: ProjectReviewsService) {
    }

    public resolve(route: ActivatedRouteSnapshot): Observable<ProjectReviewsViewModel> {
        return this.reviewsService.getReviews(
            toNumber(route.paramMap.get('id')),
            filter(
                {
                    search: route.queryParamMap.get('search') ?? undefined,
                    orderBy: route.queryParamMap.get('orderBy') ?? undefined,
                    page: route.queryParamMap.get('page') ?? undefined
                }
            )
        );
    }
}
