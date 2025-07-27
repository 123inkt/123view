import {Inject, Injectable} from '@angular/core';
import {Resolve} from '@angular/router';
import ReviewActivitiesViewModel from '@model/viewmodels/ReviewActivitiesViewModel';
import {ReviewActivitiesService} from '@service/api/review-activities.service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ReviewActivitiesViewModelResolver implements Resolve<ReviewActivitiesViewModel> {
    constructor(@Inject(ReviewActivitiesService) private readonly timelineService: ReviewActivitiesService) {
    }

    public resolve(): Observable<ReviewActivitiesViewModel> {
        return this.timelineService.getReviewActivities();
    }
}
