import {Component, input, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute, Router} from '@angular/router';
import {CodeReviewsSearch} from '@component/code-reviews-search/code-reviews-search';
import {environment} from '@environment/environment';
import ReviewsSearchModel from '@model/forms/ReviewsSearchModel';
import ProjectReviewsViewModel from '@model/viewmodels/ProjectReviewsViewModel';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'app-project-reviews',
    imports: [TranslatePipe, CodeReviewsSearch],
    templateUrl: './project-reviews.html',
    styleUrl: './project-reviews.scss'
})
export class ProjectReviews implements OnInit {
    public id = input.required<number>();
    public declare reviewsViewModel: ProjectReviewsViewModel;
    public declare reviewsSearchModel: ReviewsSearchModel;

    constructor(private readonly title: Title, private readonly route: ActivatedRoute, private readonly router: Router) {
        this.route.queryParams.subscribe((params) => {
            this.reviewsSearchModel = {...{search: 'state:open ', orderBy: 'update-timestamp'}, ...params};
        });
    }

    public ngOnInit(): void {
        this.reviewsViewModel = this.route.snapshot.data['reviewsViewModel'];
        this.title.setTitle(this.reviewsViewModel.repository.displayName + ' - ' + environment.appName);
    }

    public onSearch(): void {
        this.router.navigate([], {relativeTo: this.route, queryParams: this.reviewsSearchModel});
    }
}
