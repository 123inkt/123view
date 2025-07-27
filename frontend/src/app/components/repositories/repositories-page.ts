import {Component, OnInit} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {ActivatedRoute} from '@angular/router';
import {ActivityList} from '@component/activity-list/activity-list';
import {RepositoriesSection} from '@component/repositories/section/repositories-section';
import Repository from '@model/entities/Repository';
import ActivitiesViewModel from '@model/viewmodels/ActivitiesViewModel';
import RepositoriesViewModel from '@model/viewmodels/RepositoriesViewModel';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'app-repositories-page',
    imports: [RepositoriesSection, ActivityList, FormsModule, TranslatePipe],
    templateUrl: './repositories-page.html',
    styleUrl: './repositories-page.scss'
})
export class RepositoriesPage implements OnInit {
    public declare projectsViewModel: RepositoriesViewModel;
    public declare timelineViewModel: ActivitiesViewModel;
    public searchQuery = '';

    constructor(private readonly route: ActivatedRoute) {
    }

    public ngOnInit(): void {
        this.projectsViewModel = this.route.snapshot.data['projectsViewModel'];
        this.timelineViewModel = this.route.snapshot.data['timelineViewModel'];
    }

    public getFavoriteRepositories(): Repository[] {
        return this.projectsViewModel.repositories.filter(repo => repo.favorite);
    }

    public getRepositories(): Repository[] {
        return this.projectsViewModel.repositories.filter(repo => repo.favorite !== true);
    }
}
