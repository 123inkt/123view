import {Inject, Injectable} from '@angular/core';
import {Resolve} from '@angular/router';
import RepositoriesViewModel from '@model/viewmodels/RepositoriesViewModel';
import {ProjectsService} from '@service/api/projects-service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class RepositoriesViewModelResolver implements Resolve<RepositoriesViewModel> {
    constructor(@Inject(ProjectsService) private readonly projectsService: ProjectsService) {
    }

    public resolve(): Observable<RepositoriesViewModel> {
        return this.projectsService.getProjects();
    }
}
