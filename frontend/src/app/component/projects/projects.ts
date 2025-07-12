import {Component} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {ActivatedRoute} from '@angular/router';
import {ProjectsSection} from '@component/projects/section/projects-section';
import {Timeline} from '@component/timeline/timeline';
import Repository from '@model/entities/Repository';
import ProjectsViewModel from '@model/viewmodels/ProjectsViewModel';
import TimelineViewModel from '@model/viewmodels/TimelineViewModel';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
  selector: 'app-projects',
  imports: [ProjectsSection, Timeline, FormsModule, TranslatePipe],
  templateUrl: './projects.html',
  styleUrl: './projects.scss'
})
export class Projects {
  public declare projectsViewModel: ProjectsViewModel;
  public declare timelineViewModel: TimelineViewModel;
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
