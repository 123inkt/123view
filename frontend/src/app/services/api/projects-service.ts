import {HttpClient, HttpContext} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import RepositoriesViewModel from '@model/viewmodels/RepositoriesViewModel';
import HttpClientContext from '@service/http-client-context';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectsService {
    constructor(@Inject(HttpClient) private httpClient: HttpClient) {
    }

    public getProjects(): Observable<RepositoriesViewModel> {
        const context = new HttpContext().set(HttpClientContext.BackendApi, true);

        return this.httpClient.get<RepositoriesViewModel>('api/view-model/projects', {context});
    }
}
