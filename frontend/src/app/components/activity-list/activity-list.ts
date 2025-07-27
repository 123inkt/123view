import {AsyncPipe, DatePipe} from '@angular/common';
import {AfterContentInit, Component, ElementRef, HostListener, input} from '@angular/core';
import CodeReviewActivity from '@model/entities/CodeReviewActivity';
import ReviewActivitiesViewModel from '@model/viewmodels/ReviewActivitiesViewModel';
import {ReviewActivityFormatter} from '@service/review/review-activity-formatter';
import {Observable} from 'rxjs';

@Component({
    host: {
        '[style.position]': 'hostPosition',
        '[style.width]': 'hostWidth',
        '[style.height]': 'hostHeight'
    },
    selector: 'app-activity-list',
    imports: [AsyncPipe, DatePipe],
    templateUrl: './activity-list.html',
    styleUrl: './activity-list.scss'
})
// TODO add comment and comment reply
export class ActivityList implements AfterContentInit {
    public viewModel    = input.required<ReviewActivitiesViewModel>();
    public hostPosition = '';
    public hostWidth    = '';
    public hostHeight   = '';
    private offsetTop   = 0;
    private width       = 0;

    constructor(private readonly elRef: ElementRef, private readonly activityFormatter: ReviewActivityFormatter) {
    }

    public ngAfterContentInit() {
        this.offsetTop = this.elRef.nativeElement.offsetTop;
        this.width     = this.elRef.nativeElement.clientWidth;
        this.layout();
    }

    public formatMessage(activity: CodeReviewActivity): Observable<string> {
        return this.activityFormatter.formatActivity(activity);
    }

    @HostListener('window:resize')
    @HostListener('window:scroll')
    public layout(): void {
        if (window.scrollY <= this.offsetTop) {
            this.hostPosition = '';
            this.hostWidth    = '';
            this.hostHeight   = String(window.innerHeight - this.offsetTop + window.scrollY) + 'px';
        } else {
            this.hostPosition = 'fixed';
            this.hostWidth    = String(this.width) + 'px';
            this.hostHeight   = String(window.innerHeight) + 'px';
        }
    }
}
