import {Component} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {ActivatedRoute} from '@angular/router';
import {ProjectsSection} from '@component/projects/section/projects-section';
import Repository from '@model/entities/Repository';
import ProjectsViewModel from '@model/viewmodels/ProjectsViewModel';

@Component({
  selector: 'app-projects',
  imports: [ProjectsSection, FormsModule],
  templateUrl: './projects.html',
  styleUrl: './projects.scss'
})
export class Projects {
  public declare projectsViewModel: ProjectsViewModel;
  public searchQuery = '';

  constructor(private readonly route: ActivatedRoute) {
  }

  public ngOnInit(): void {
    this.projectsViewModel = this.route.snapshot.data['resolvedData'];
  }

  public getFavoriteRepositories(): Repository[] {
    return this.projectsViewModel.repositories.filter(repo => repo.favorite);
  }

  public getRepositories(): Repository[] {
    return this.projectsViewModel.repositories.filter(repo => repo.favorite !== true);
  }

}
