import {HttpClient} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import TimelineViewModel from '@model/viewmodels/TimelineViewModel';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectsTimelineService {
    constructor(@Inject(HttpClient) private httpClient: HttpClient) {
    }

    public getTimeline(): Observable<TimelineViewModel> {
        return this.httpClient.get<TimelineViewModel>('api/view-model/projects/timeline');
    }
}
