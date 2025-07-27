import {Component, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute, RouterLink} from '@angular/router';
import {Paginator} from '@component/paginator/paginator';
import {RevisionList} from '@component/revisions/revision-list/revision-list';
import {SearchBar} from '@component/search-bar/search-bar';
import {environment} from '@environment/environment';
import SearchModel from '@model/forms/SearchModel';
import RepositoryRevisionListViewModel from '@model/viewmodels/RepositoryRevisionListViewModel';
import {TranslatePipe} from '@ngx-translate/core';

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
        private readonly route: ActivatedRoute
        // private readonly router: Router,
        // private readonly reviewsService: ReviewListService
    ) {
        // this.route.queryParams
        //     .pipe(
        //         tap((params) => this.reviewsSearchModel = {...ReviewList.DefaultSearch, ...params}),
        //         skip(1), // Ignore the initial queryParams emission,
        //         switchMap((params) => this.reviewsService.getReviews(this.id(), params))
        //     )
        //     .subscribe((viewModel) => this.reviewListViewModel = viewModel);
    }

    public ngOnInit(): void {
        this.revisionListViewModel = this.route.snapshot.data['revisionListViewModel'];
        this.title.setTitle(this.revisionListViewModel.repository.displayName + ' revisions - ' + environment.appName);
    }
}
