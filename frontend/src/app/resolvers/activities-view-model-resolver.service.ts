import {Inject, Injectable} from '@angular/core';
import {Resolve} from '@angular/router';
import ActivitiesViewModel from '@model/viewmodels/ActivitiesViewModel';
import {ProjectsTimelineService} from '@service/api/projects-timeline-service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ActivitiesViewModelResolver implements Resolve<ActivitiesViewModel> {
    constructor(@Inject(ProjectsTimelineService) private readonly timelineService: ProjectsTimelineService) {
    }

    public resolve(): Observable<ActivitiesViewModel> {
        return this.timelineService.getTimeline();
    }
}
