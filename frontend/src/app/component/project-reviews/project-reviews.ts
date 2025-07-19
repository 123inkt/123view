import {Component, input, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute} from '@angular/router';
import {environment} from '@environment/environment';
import ProjectReviewsViewModel from '@model/viewmodels/ProjectReviewsViewModel';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'app-project-reviews',
    imports: [TranslatePipe],
    templateUrl: './project-reviews.html',
    styleUrl: './project-reviews.scss'
})
export class ProjectReviews implements OnInit {
    public id = input.required<number>();
    public declare reviewsViewModel: ProjectReviewsViewModel;

    constructor(private readonly title: Title, private readonly route: ActivatedRoute) {
    }

    public ngOnInit(): void {
        this.reviewsViewModel = this.route.snapshot.data['reviewsViewModel'];
        this.title.setTitle(this.reviewsViewModel.repository.displayName + ' - ' + environment.appName);
    }
}
