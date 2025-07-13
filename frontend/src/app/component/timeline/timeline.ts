import {AsyncPipe, DatePipe} from '@angular/common';
import {Component, input} from '@angular/core';
import CodeReviewActivity from '@model/entities/CodeReviewActivity';
import TimelineViewModel from '@model/viewmodels/TimelineViewModel';
import {ReviewActivityFormatter} from '@service/review/review-activity-formatter';
import {Observable} from 'rxjs';

@Component({
    selector: 'app-timeline',
    imports: [AsyncPipe, DatePipe],
    templateUrl: './timeline.html',
    styleUrl: './timeline.scss'
})
export class Timeline {
    public viewModel = input.required<TimelineViewModel>();

    constructor(private readonly activityFormatter: ReviewActivityFormatter) {
    }

    public formatMessage(activity: CodeReviewActivity): Observable<string> {
        return this.activityFormatter.formatActivity(activity);
    }
}
