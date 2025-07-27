import {Component, input} from '@angular/core';
import {RouterLink} from '@angular/router';
import CodeReview from '@model/entities/CodeReview';
import Repository from '@model/entities/Repository';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'app-branch-list',
    imports: [TranslatePipe, RouterLink],
    templateUrl: './branch-list.html',
    styleUrls: ['./branch-list.scss']
})
export class BranchList {
    public branches       = input.required<string[]>();
    public mergedBranches = input.required<string[]>();
    public repository     = input.required<Repository>();
    public reviews        = input.required<Record<string, CodeReview>>();

    public isMerged(branch: string): boolean {
        return this.mergedBranches().includes(branch);
    }
}
