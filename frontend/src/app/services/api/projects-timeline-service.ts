import {HttpClient, HttpContext} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import TimelineViewModel from '@model/viewmodels/TimelineViewModel';
import HttpClientContext from '@service/http-client-context';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectsTimelineService {
    constructor(@Inject(HttpClient) private httpClient: HttpClient) {
    }

    public getTimeline(repositoryId?: number): Observable<TimelineViewModel> {
        const context = new HttpContext().set(HttpClientContext.BackendApi, true);
        const params  = repositoryId !== undefined ? {repositoryId} : undefined;

        return this.httpClient.get<TimelineViewModel>('api/view-model/projects/timeline', {context, params});
    }
}
