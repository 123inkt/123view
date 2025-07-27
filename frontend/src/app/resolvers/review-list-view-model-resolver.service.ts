import {Inject, Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve} from '@angular/router';
import {toNumber} from '@lib/Numbers';
import {filter} from '@lib/Objects';
import ReviewListViewModel from '@model/viewmodels/ReviewListViewModel';
import {ReviewListService} from '@service/api/review-list.service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ReviewListViewModelResolver implements Resolve<ReviewListViewModel> {
    constructor(@Inject(ReviewListService) private readonly reviewsService: ReviewListService) {
    }

    public resolve(route: ActivatedRouteSnapshot): Observable<ReviewListViewModel> {
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
