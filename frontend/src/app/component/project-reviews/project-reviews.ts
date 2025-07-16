import {Component, input, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';

@Component({
    selector: 'app-project-reviews',
    imports: [],
    templateUrl: './project-reviews.html',
    styleUrl: './project-reviews.scss'
})
export class ProjectReviews implements OnInit {
    public id = input.required<number>();
    public reviewsViewModel: unknown;

    constructor(private readonly route: ActivatedRoute) {
    }

    public ngOnInit(): void {
        this.reviewsViewModel = this.route.snapshot.data['projectsViewModel'];
    }
}
