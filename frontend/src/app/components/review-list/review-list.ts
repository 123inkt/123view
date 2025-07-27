import {Component, input, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute, Router} from '@angular/router';
import {ActivityList} from '@component/activity-list/activity-list';
import {Paginator} from '@component/paginator/paginator';
import {ReviewListSearch} from '@component/review-list-search/review-list-search';
import {environment} from '@environment/environment';
import ReviewsSearchModel from '@model/forms/ReviewsSearchModel';
import ActivitiesViewModel from '@model/viewmodels/ActivitiesViewModel';
import ReviewListViewModel from '@model/viewmodels/ReviewListViewModel';
import {TranslatePipe} from '@ngx-translate/core';
import {ProjectReviewsService} from '@service/api/project-reviews-service';
import {skip, switchMap, tap} from 'rxjs';

@Component({
    selector: 'app-review-list',
    imports: [TranslatePipe, ReviewListSearch, Paginator, ActivityList],
    templateUrl: './review-list.html',
    styleUrl: './review-list.scss'
})
export class ReviewList implements OnInit {
    private static readonly DefaultSearch: ReviewsSearchModel = {search: 'state:open ', orderBy: 'update-timestamp'};

    public id                 = input.required<number>();
    public declare reviewsViewModel: ReviewListViewModel;
    public declare timelineViewModel: ActivitiesViewModel;
    public reviewsSearchModel = ReviewList.DefaultSearch;

    constructor(
        private readonly title: Title,
        private readonly route: ActivatedRoute,
        private readonly router: Router,
        private readonly reviewsService: ProjectReviewsService
    ) {
        this.route.queryParams
            .pipe(
                tap((params) => this.reviewsSearchModel = {...ReviewList.DefaultSearch, ...params}),
                skip(1), // Ignore the initial queryParams emission,
                switchMap((params) => this.reviewsService.getReviews(this.id(), params))
            )
            .subscribe((viewModel) => this.reviewsViewModel = viewModel);
    }

    public ngOnInit(): void {
        this.reviewsViewModel  = this.route.snapshot.data['reviewsViewModel'];
        this.timelineViewModel = this.route.snapshot.data['timelineViewModel'];
        this.title.setTitle(this.reviewsViewModel.repository.displayName + ' - ' + environment.appName);
    }

    public onSearch(): void {
        this.router.navigate([], {relativeTo: this.route, queryParams: {...this.reviewsSearchModel, ...{page: 1}}});
    }

    public onPaginate(page: number): void {
        this.router.navigate([], {relativeTo: this.route, queryParams: {page}, queryParamsHandling: 'merge'})
            .then(() => window.scrollTo(0, 0));
    }
}
