import {HttpClient, HttpContext} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import ProjectsViewModel from '@model/viewmodels/ProjectsViewModel';
import HttpClientContext from '@service/http-client-context';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectsService {
    constructor(@Inject(HttpClient) private httpClient: HttpClient) {
    }

    public getProjects(): Observable<ProjectsViewModel> {
        const context = new HttpContext().set(HttpClientContext.BackendApi, true);

        return this.httpClient.get<ProjectsViewModel>('api/view-model/projects', {context});
    }
}
