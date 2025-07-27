import {Component, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute, Router, RouterLink} from '@angular/router';
import {Paginator} from '@component/paginator/paginator';
import {RevisionList} from '@component/revisions/revision-list/revision-list';
import {SearchBar} from '@component/search-bar/search-bar';
import {environment} from '@environment/environment';
import SearchModel from '@model/forms/SearchModel';
import RepositoryRevisionListViewModel from '@model/viewmodels/RepositoryRevisionListViewModel';
import {TranslatePipe} from '@ngx-translate/core';
import {RepositoryRevisionListService} from '@service/api/repository-revision-list-service';
import {skip, switchMap, tap} from 'rxjs';

@Component({
    selector: 'app-revisions-page',
    imports: [
        TranslatePipe,
        RouterLink,
        RevisionList,
        Paginator,
        SearchBar
    ],
    templateUrl: './revisions-page.html'
})
export class RevisionsPage implements OnInit {
    public declare revisionListViewModel: RepositoryRevisionListViewModel;
    public searchModel: SearchModel = {search: ''};

    constructor(
        private readonly title: Title,
        private readonly route: ActivatedRoute,
        private readonly router: Router,
        private readonly revisionsService: RepositoryRevisionListService
    ) {
        this.route.queryParams
            .pipe(
                tap((params) => this.searchModel = {...{search: ''}, ...params}),
                skip(1), // Ignore the initial queryParams emission,
                switchMap((params) => this.revisionsService.getRevisions(this.revisionListViewModel.repository.id, params))
            )
            .subscribe((viewModel) => this.revisionListViewModel = viewModel);
    }

    public ngOnInit(): void {
        this.revisionListViewModel = this.route.snapshot.data['revisionListViewModel'];
        this.title.setTitle(this.revisionListViewModel.repository.displayName + ' revisions - ' + environment.appName);
    }

    public onSearch(): void {
        this.router.navigate([], {relativeTo: this.route, queryParams: {...this.searchModel, ...{page: 1}}});
    }

    public onPaginate(page: number): void {
        this.router.navigate([], {relativeTo: this.route, queryParams: {page}, queryParamsHandling: 'merge'})
            .then(() => window.scrollTo(0, 0));
    }
}
