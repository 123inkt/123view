import {Component, input, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute, Router} from '@angular/router';
import {CodeReviewsSearch} from '@component/code-reviews-search/code-reviews-search';
import {Paginator} from '@component/paginator/paginator';
import {environment} from '@environment/environment';
import ReviewsSearchModel from '@model/forms/ReviewsSearchModel';
import ProjectReviewsViewModel from '@model/viewmodels/ProjectReviewsViewModel';
import {TranslatePipe} from '@ngx-translate/core';
import {ProjectReviewsService} from '@service/api/project-reviews-service';
import {skip, switchMap, tap} from 'rxjs';

@Component({
    selector: 'app-project-reviews',
    imports: [TranslatePipe, CodeReviewsSearch, Paginator],
    templateUrl: './project-reviews.html',
    styleUrl: './project-reviews.scss'
})
export class ProjectReviews implements OnInit {
    private static readonly DefaultSearch: ReviewsSearchModel = {search: 'state:open ', orderBy: 'update-timestamp'};

    public id                 = input.required<number>();
    public declare reviewsViewModel: ProjectReviewsViewModel;
    public reviewsSearchModel = ProjectReviews.DefaultSearch;

    constructor(
        private readonly title: Title,
        private readonly route: ActivatedRoute,
        private readonly router: Router,
        private readonly reviewsService: ProjectReviewsService
    ) {
        this.route.queryParams
            .pipe(
                tap((params) => this.reviewsSearchModel = {...ProjectReviews.DefaultSearch, ...params}),
                skip(1), // Ignore the initial queryParams emission,
                switchMap((params) => this.reviewsService.getReviews(this.id(), params))
            )
            .subscribe((viewModel) => this.reviewsViewModel = viewModel);
    }

    public ngOnInit(): void {
        this.reviewsViewModel = this.route.snapshot.data['reviewsViewModel'];
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
