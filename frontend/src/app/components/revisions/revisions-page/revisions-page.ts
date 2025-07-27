import {Component, input, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute} from '@angular/router';
import {Paginator} from '@component/paginator/paginator';
import {RepositoryMenuBar} from '@component/repository/repository-menu-bar/repository-menu-bar';
import {RevisionList} from '@component/revisions/revision-list/revision-list';
import {SearchBar} from '@component/search-bar/search-bar';
import {environment} from '@environment/environment';
import SearchModel from '@model/forms/SearchModel';
import RepositoryRevisionListViewModel from '@model/viewmodels/RepositoryRevisionListViewModel';
import {TranslatePipe} from '@ngx-translate/core';
import {RepositoryRevisionListService} from '@service/api/repository-revision-list-service';
import NavigatorService from '@service/navigator-service';
import {skip, switchMap, tap} from 'rxjs';

@Component({
    selector: 'app-revisions-page',
    imports: [TranslatePipe, RevisionList, Paginator, SearchBar, RepositoryMenuBar],
    templateUrl: './revisions-page.html'
})
export class RevisionsPage implements OnInit {
    public id                       = input.required<number>();
    public declare revisionListViewModel: RepositoryRevisionListViewModel;
    public searchModel: SearchModel = {search: ''};

    constructor(
        private readonly title: Title,
        private readonly route: ActivatedRoute,
        private readonly navigator: NavigatorService,
        private readonly revisionsService: RepositoryRevisionListService
    ) {
        this.route.queryParams
            .pipe(
                tap((params) => this.searchModel = {...{search: ''}, ...params}),
                skip(1), // Ignore the initial queryParams emission,
                switchMap((params) => this.revisionsService.getRevisions(this.id(), params))
            )
            .subscribe((viewModel) => this.revisionListViewModel = viewModel);
    }

    public ngOnInit(): void {
        this.revisionListViewModel = this.route.snapshot.data['revisionListViewModel'];
        this.title.setTitle(this.revisionListViewModel.repository.displayName + ' - revisions - ' + environment.appName);
    }

    public onSearch(): void {
        this.navigator.navigateToQuery({...this.searchModel, ...{page: 1}});
    }

    public onPaginate(page: number): void {
        this.navigator.combineWithQuery({page});
        window.scrollTo(0, 0);
    }
}
