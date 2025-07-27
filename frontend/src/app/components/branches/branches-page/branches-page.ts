import {Component, input, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute} from '@angular/router';
import {RepositoryMenuBar} from '@component/repository/repository-menu-bar/repository-menu-bar';
import {environment} from '@environment/environment';
import SearchModel from '@model/forms/SearchModel';
import RepositoryBranchListViewModel from '@model/viewmodels/RepositoryBranchListViewModel';

@Component({
    selector: 'app-branches-page',
    imports: [RepositoryMenuBar],
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
}
