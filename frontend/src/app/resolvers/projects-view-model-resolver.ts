import {Inject, Injectable} from '@angular/core';
import {Resolve} from '@angular/router';
import ProjectsViewModel from '@model/viewmodels/ProjectsViewModel';
import {ProjectsService} from '@service/api/projects-service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProjectsViewModelResolver implements Resolve<ProjectsViewModel> {
    constructor(@Inject(ProjectsService) private readonly projectsService: ProjectsService) {
    }

    public resolve(): Observable<ProjectsViewModel> {
        return this.projectsService.getProjects();
    }
}
