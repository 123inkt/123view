import {Component, input, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute} from '@angular/router';
import {BranchList} from '@component/branches/branch-list/branch-list';
import {RepositoryMenuBar} from '@component/repository/repository-menu-bar/repository-menu-bar';
import {SearchBar} from '@component/search-bar/search-bar';
import {environment} from '@environment/environment';
import SearchModel from '@model/forms/SearchModel';
import RepositoryBranchListViewModel from '@model/viewmodels/RepositoryBranchListViewModel';

@Component({
    selector: 'app-branches-page',
    imports: [RepositoryMenuBar, BranchList, SearchBar],
    templateUrl: './branches-page.html'
})
export class BranchesPage implements OnInit {
    public id                       = input.required<number>();
    public searchModel: SearchModel = {search: ''};
    public declare branchListViewModel: RepositoryBranchListViewModel;

    constructor(
        private readonly title: Title,
        private readonly route: ActivatedRoute
    ) {
        this.route.queryParams.subscribe((params) => this.searchModel.search = params['search'] ?? '');
    }

    public ngOnInit(): void {
        this.branchListViewModel = this.route.snapshot.data['branchListViewModel'];
        this.title.setTitle(this.branchListViewModel.repository.displayName + ' - branches - ' + environment.appName);
    }

    public getBranches(): string[] {
        // TODO split words and match on all words
        return this.branchListViewModel.branches
            .filter((branch) => branch.toLowerCase().includes(this.searchModel.search.toLowerCase()));
    }
}
