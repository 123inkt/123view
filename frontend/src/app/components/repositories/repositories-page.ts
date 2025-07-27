import {Component, OnInit} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {ActivatedRoute} from '@angular/router';
import {ActivityList} from '@component/activity-list/activity-list';
import {RepositoriesSection} from '@component/repositories/section/repositories-section';
import Repository from '@model/entities/Repository';
import RepositoriesViewModel from '@model/viewmodels/RepositoriesViewModel';
import ReviewActivitiesViewModel from '@model/viewmodels/ReviewActivitiesViewModel';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'app-repositories-page',
    imports: [RepositoriesSection, ActivityList, FormsModule, TranslatePipe],
    templateUrl: './repositories-page.html',
    styleUrl: './repositories-page.scss'
})
export class RepositoriesPage implements OnInit {
    public declare repositoriesViewModel: RepositoriesViewModel;
    public declare reviewActivitiesViewModel: ReviewActivitiesViewModel;
    public searchQuery = '';

    constructor(private readonly route: ActivatedRoute) {
    }

    public ngOnInit(): void {
        this.repositoriesViewModel     = this.route.snapshot.data['repositoriesViewModel'];
        this.reviewActivitiesViewModel = this.route.snapshot.data['reviewActivitiesViewModel'];
    }

    public getFavoriteRepositories(): Repository[] {
        return this.repositoriesViewModel.repositories.filter(repo => repo.favorite);
    }

    public getRepositories(): Repository[] {
        return this.repositoriesViewModel.repositories.filter(repo => repo.favorite !== true);
    }
}
