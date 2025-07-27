import {Inject, Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve} from '@angular/router';
import {toNumber} from '@lib/Numbers';
import RepositoryBranchListViewModel from '@model/viewmodels/RepositoryBranchListViewModel';
import {RepositoryBranchListService} from '@service/api/repository-branch-list-service';
import {Observable} from 'rxjs';

@Injectable({providedIn: 'root'})
export class RepositoryBranchListViewModelResolverService implements Resolve<RepositoryBranchListViewModel> {
    constructor(@Inject(RepositoryBranchListService) private readonly branchListService: RepositoryBranchListService) {
    }

    public resolve(route: ActivatedRouteSnapshot): Observable<RepositoryBranchListViewModel> {
        return this.branchListService.getBranches(toNumber(route.paramMap.get('id')));
    }
}
