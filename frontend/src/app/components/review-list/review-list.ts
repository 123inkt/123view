import {Component, input, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute} from '@angular/router';
import {ActivityList} from '@component/activity-list/activity-list';
import {Paginator} from '@component/paginator/paginator';
import {RepositoryMenuBar} from '@component/repository/repository-menu-bar/repository-menu-bar';
import {ReviewListSearch} from '@component/review-list-search/review-list-search';
import {environment} from '@environment/environment';
import ReviewsSearchModel from '@model/forms/ReviewsSearchModel';
import ReviewActivitiesViewModel from '@model/viewmodels/ReviewActivitiesViewModel';
import ReviewListViewModel from '@model/viewmodels/ReviewListViewModel';
import {TranslatePipe} from '@ngx-translate/core';
import {ReviewListService} from '@service/api/review-list.service';
import NavigatorService from '@service/navigator-service';
import {skip, switchMap, tap} from 'rxjs';

@Component({
    selector: 'app-review-list',
    imports: [TranslatePipe, ReviewListSearch, Paginator, ActivityList, RepositoryMenuBar],
    templateUrl: './review-list.html',
    styleUrl: './review-list.scss'
})
export class ReviewList implements OnInit {
    private static readonly DefaultSearch: ReviewsSearchModel = {search: 'state:open ', orderBy: 'update-timestamp'};

    public id                 = input.required<number>();
    public declare reviewListViewModel: ReviewListViewModel;
    public declare reviewActivitiesViewModel: ReviewActivitiesViewModel;
    public reviewsSearchModel = ReviewList.DefaultSearch;

    constructor(
        private readonly title: Title,
        private readonly route: ActivatedRoute,
        private readonly navigator: NavigatorService,
        private readonly reviewsService: ReviewListService
    ) {
        this.route.queryParams
            .pipe(
                tap((params) => this.reviewsSearchModel = {...ReviewList.DefaultSearch, ...params}),
                skip(1), // Ignore the initial queryParams emission,
                switchMap((params) => this.reviewsService.getReviews(this.id(), params))
            )
            .subscribe((viewModel) => this.reviewListViewModel = viewModel);
    }

    public ngOnInit(): void {
        this.reviewListViewModel       = this.route.snapshot.data['reviewListViewModel'];
        this.reviewActivitiesViewModel = this.route.snapshot.data['reviewActivitiesViewModel'];
        this.title.setTitle(this.reviewListViewModel.repository.displayName + ' - ' + environment.appName);
    }

    public onSearch(): void {
        this.navigator.navigateToQuery({...this.reviewsSearchModel, ...{page: 1}});
    }

    public onPaginate(page: number): void {
        this.navigator.combineWithQuery({page});
        window.scrollTo(0, 0);
    }
}
