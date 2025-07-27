import {Inject, Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve} from '@angular/router';
import {toNumber} from '@lib/Numbers';
import TimelineViewModel from '@model/viewmodels/TimelineViewModel';
import {ProjectsTimelineService} from '@service/api/projects-timeline-service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectReviewsTimelineViewModelResolver implements Resolve<TimelineViewModel> {
    constructor(@Inject(ProjectsTimelineService) private readonly timelineService: ProjectsTimelineService) {
    }

    public resolve(route: ActivatedRouteSnapshot): Observable<TimelineViewModel> {
        return this.timelineService.getTimeline(toNumber(route.paramMap.get('id')));
    }
}
