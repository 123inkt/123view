import {HttpClient, HttpContext} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import ActivitiesViewModel from '@model/viewmodels/ActivitiesViewModel';
import HttpClientContext from '@service/http-client-context';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectsTimelineService {
    constructor(@Inject(HttpClient) private httpClient: HttpClient) {
    }

    public getTimeline(repositoryId?: number): Observable<ActivitiesViewModel> {
        const context = new HttpContext().set(HttpClientContext.BackendApi, true);
        const params  = repositoryId !== undefined ? {repositoryId} : undefined;

        return this.httpClient.get<ActivitiesViewModel>('api/view-model/projects/timeline', {context, params});
    }
}
