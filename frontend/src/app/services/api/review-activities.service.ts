import {HttpClient, HttpContext} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import ReviewActivitiesViewModel from '@model/viewmodels/ReviewActivitiesViewModel';
import HttpClientContext from '@service/http-client-context';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ReviewActivitiesService {
    constructor(@Inject(HttpClient) private httpClient: HttpClient) {
    }

    public getReviewActivities(repositoryId?: number): Observable<ReviewActivitiesViewModel> {
        const context = new HttpContext().set(HttpClientContext.BackendApi, true);
        const params  = repositoryId !== undefined ? {repositoryId} : undefined;

        return this.httpClient.get<ReviewActivitiesViewModel>('api/view-model/projects/timeline', {context, params});
    }
}
