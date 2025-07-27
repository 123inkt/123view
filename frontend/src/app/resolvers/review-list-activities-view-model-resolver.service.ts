import {Inject, Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve} from '@angular/router';
import {toNumber} from '@lib/Numbers';
import ActivitiesViewModel from '@model/viewmodels/ActivitiesViewModel';
import {ProjectsTimelineService} from '@service/api/projects-timeline-service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ReviewListActivitiesViewModelResolver implements Resolve<ActivitiesViewModel> {
    constructor(@Inject(ProjectsTimelineService) private readonly timelineService: ProjectsTimelineService) {
    }

    public resolve(route: ActivatedRouteSnapshot): Observable<ActivitiesViewModel> {
        return this.timelineService.getTimeline(toNumber(route.paramMap.get('id')));
    }
}
