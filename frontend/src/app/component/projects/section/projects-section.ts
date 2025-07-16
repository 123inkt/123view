import {DecimalPipe} from '@angular/common';
import {Component, input, OnChanges, SimpleChanges} from '@angular/core';
import {RouterLink} from '@angular/router';
import {contains} from '@lib/Strings';
import Repository from '@model/entities/Repository';

@Component({
    selector: 'app-projects-section',
    imports: [DecimalPipe, RouterLink],
    templateUrl: './projects-section.html',
    styleUrl: './projects-section.scss'
})
export class ProjectsSection implements OnChanges {
    public repositories                      = input.required<Repository[]>();
    public revisionCount                     = input.required<Record<number, number>>();
    public searchQuery                       = input.required<string>();
    public repositoryColumns: Repository[][] = [];

    public ngOnChanges(changes: SimpleChanges) {
        if (changes['searchQuery'] || changes['repositories']) {
            this.updateRepositoryColumns();
        }
    }

    private updateRepositoryColumns(): void {
        const searchQuery  = this.searchQuery().toLowerCase().trim();
        const repositories = this.repositories().filter(repo => contains(repo.displayName, searchQuery));
        const columnSize   = Math.ceil(repositories.length / 3);

        const batches: Repository[][] = [];
        for (let i = 0; i < repositories.length; i += columnSize) {
            batches.push(repositories.slice(i, i + columnSize));
        }

        this.repositoryColumns = batches;
    }
}
