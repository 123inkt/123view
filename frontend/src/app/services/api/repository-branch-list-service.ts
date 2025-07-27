import {HttpClient, HttpContext} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import RepositoryBranchListViewModel from '@model/viewmodels/RepositoryBranchListViewModel';
import HttpClientContext from '@service/http-client-context';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class RepositoryBranchListService {
    constructor(@Inject(HttpClient) private httpClient: HttpClient) {
    }

    public getBranches(repositoryId: number): Observable<RepositoryBranchListViewModel> {
        const context = new HttpContext().set(HttpClientContext.BackendApi, true);

        return this.httpClient.get<RepositoryBranchListViewModel>('api/view-model/branches/' + String(repositoryId), {context});
    }
}
