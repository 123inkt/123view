import {HttpClient, HttpContext} from '@angular/common/http';
import {Injectable} from '@angular/core';
import ProjectReviewsViewModel from '@model/viewmodels/ProjectReviewsViewModel';
import HttpClientContext from '@service/http-client-context';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectReviewsService {
    constructor(private readonly httpClient: HttpClient) {
    }

    public getReviews(
        repositoryId: number,
        filter: {search?: string, 'order-by'?: string, page?: number | string},
        progressIndicator = false
    ): Observable<ProjectReviewsViewModel> {
        const context = new HttpContext()
            .set(HttpClientContext.BackendApi, true)
            .set(HttpClientContext.ProgressIndicator, progressIndicator);

        return this.httpClient.get<ProjectReviewsViewModel>('api/view-model/reviews/' + String(repositoryId), {context, params: filter});
    }
}
