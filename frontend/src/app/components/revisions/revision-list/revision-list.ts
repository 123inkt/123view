import {DatePipe, SlicePipe} from '@angular/common';
import {Component, input} from '@angular/core';
import {RouterLink} from '@angular/router';
import Repository from '@model/entities/Repository';
import Revision from '@model/entities/Revision';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'app-revision-list',
    imports: [
        DatePipe,
        TranslatePipe,
        SlicePipe,
        RouterLink
    ],
    templateUrl: './revision-list.html',
    styleUrl: './revision-list.scss'
})
export class RevisionList {
    public repository = input.required<Repository>();
    public revisions  = input.required<Revision[]>();
    public reviewIds  = input.required<Record<number, number>>();

    public getReviewProjectId(revision: Revision): number | null {
        return this.reviewIds()[revision.id] ?? null;
    }
}
