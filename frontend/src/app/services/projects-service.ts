import {HttpClient} from '@angular/common/http';
import {Inject, Injectable} from '@angular/core';
import ProjectsViewModel from '@model/ProjectsViewModel';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectsService {
  constructor(@Inject(HttpClient) private httpClient: HttpClient) {
  }

  public getProjects(): Observable<ProjectsViewModel> {
    return this.httpClient.get<ProjectsViewModel>('/app/projects.json');
  }
}
