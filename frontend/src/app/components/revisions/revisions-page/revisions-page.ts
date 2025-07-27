import {Component, OnInit} from '@angular/core';
import {Title} from '@angular/platform-browser';
import {ActivatedRoute, RouterLink} from '@angular/router';
import {environment} from '@environment/environment';
import RepositoryRevisionListViewModel from '@model/viewmodels/RepositoryRevisionListViewModel';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'app-revisions-page',
    imports: [
        TranslatePipe,
        RouterLink
    ],
    templateUrl: './revisions-page.html',
    styleUrl: './revisions-page.scss'
})
export class RevisionsPage implements OnInit {
    public declare revisionListViewModel: RepositoryRevisionListViewModel;

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
