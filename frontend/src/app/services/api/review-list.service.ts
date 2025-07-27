import {HttpClient, HttpContext} from '@angular/common/http';
import {Injectable} from '@angular/core';
import ReviewListViewModel from '@model/viewmodels/ReviewListViewModel';
import HttpClientContext from '@service/http-client-context';
import {Observable} from 'rxjs';

// eslint-disable-next-line @typescript-eslint/consistent-type-definitions
type Filter = {search?: string, 'order-by'?: string, page?: number | string};

@Injectable({providedIn: 'root'})
export class ReviewListService {
    constructor(private readonly httpClient: HttpClient) {
    }

    public getReviews(repositoryId: number, filter: Filter): Observable<ReviewListViewModel> {
        const context = new HttpContext().set(HttpClientContext.BackendApi, true);

        return this.httpClient.get<ReviewListViewModel>('api/view-model/reviews/' + String(repositoryId), {context, params: filter});
    }
}
