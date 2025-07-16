import {Inject, Injectable} from '@angular/core';
import {Resolve} from '@angular/router';
import TimelineViewModel from '@model/viewmodels/TimelineViewModel';
import {ProjectsTimelineService} from '@service/api/projects-timeline-service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectsTimelineViewModelResolver implements Resolve<TimelineViewModel> {
    constructor(@Inject(ProjectsTimelineService) private readonly timelineService: ProjectsTimelineService) {
    }

    public resolve(): Observable<TimelineViewModel> {
        return this.timelineService.getTimeline();
    }
}
