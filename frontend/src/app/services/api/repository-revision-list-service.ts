import {HttpClient, HttpContext} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import RepositoryRevisionListViewModel from '@model/viewmodels/RepositoryRevisionListViewModel';
import HttpClientContext from '@service/http-client-context';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class RepositoryRevisionListService {
    constructor(@Inject(HttpClient) private httpClient: HttpClient) {
    }


    public getRevision(repositoryId: number): Observable<RepositoryRevisionListViewModel> {
        const context = new HttpContext().set(HttpClientContext.BackendApi, true);

        return this.httpClient.get<RepositoryRevisionListViewModel>('api/view-model/revisions/' + repositoryId, {context});
    }
}
