import {Component} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import ProjectsViewModel from '@model/ProjectsViewModel';

@Component({
  selector: 'app-projects',
  imports: [],
  templateUrl: './projects.html',
  styleUrl: './projects.scss'
})
export class Projects {
  public declare projectsViewModel: ProjectsViewModel;

  constructor(private readonly route: ActivatedRoute) {
  }

  public ngOnInit(): void {
    this.projectsViewModel = this.route.snapshot.data['resolvedData'];
    console.log(this.projectsViewModel);
  }
}
