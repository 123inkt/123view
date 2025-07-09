import {DecimalPipe} from '@angular/common';
import {Component, input} from '@angular/core';
import Repository from '@model/entities/Repository';
import {ContainsPipe} from '../../../pipes/contains-pipe';

@Component({
  selector: 'app-projects-section',
  imports: [DecimalPipe, ContainsPipe],
  templateUrl: './projects-section.html',
  styleUrl: './projects-section.scss'
})
export class ProjectsSection {
  public repositories  = input.required<Repository[]>();
  public revisionCount = input.required<{ [key: number]: number }>();
  public searchQuery   = input.required<string>();

  public getBatches(): Repository[][] {
    const columnSize = Math.ceil(this.repositories().length / 3);

    const batches: Repository[][] = [];
    for (let i = 0; i < this.repositories().length; i += columnSize) {
      batches.push(this.repositories().slice(i, i + columnSize));
    }

    return batches;
  }
}
