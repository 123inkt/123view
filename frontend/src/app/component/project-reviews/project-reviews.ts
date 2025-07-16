import {Component, input} from '@angular/core';

@Component({
    selector: 'app-project-reviews',
    imports: [],
    templateUrl: './project-reviews.html',
    styleUrl: './project-reviews.scss'
})
export class ProjectReviews {
    public id = input.required<number>();
}
