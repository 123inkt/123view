import {Injectable} from '@angular/core';
import RepositoryRevisionListViewModel from '@model/viewmodels/RepositoryRevisionListViewModel';
import {Observable, of} from 'rxjs';

@Injectable({providedIn: 'root'})
export class RepositoryRevisionListService {

    public getRevision(repositoryId: number): Observable<RepositoryRevisionListViewModel> {
        return of(true);
    }
}
