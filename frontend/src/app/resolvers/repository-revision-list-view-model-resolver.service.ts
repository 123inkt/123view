import {Inject, Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve} from '@angular/router';
import {toNumber} from '@lib/Numbers';
import RepositoryRevisionListViewModel from '@model/viewmodels/RepositoryRevisionListViewModel';
import {RepositoryRevisionListService} from '@service/api/repository-revision-list-service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class RepositoryRevisionListViewModelResolver implements Resolve<RepositoryRevisionListViewModel> {
    constructor(@Inject(RepositoryRevisionListService) private readonly revisionListService: RepositoryRevisionListService) {
    }

    public resolve(route: ActivatedRouteSnapshot): Observable<RepositoryRevisionListViewModel> {
        return this.revisionListService.getRevisions(toNumber(route.paramMap.get('id')));
    }
}
