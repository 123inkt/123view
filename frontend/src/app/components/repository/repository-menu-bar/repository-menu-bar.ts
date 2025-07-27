import {Component, input} from '@angular/core';
import {RouterLink} from '@angular/router';
import Repository from '@model/entities/Repository';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'app-repository-menu-bar',
    imports: [TranslatePipe, RouterLink],
    templateUrl: './repository-menu-bar.html',
    host: {class: 'mt-2 pb-2 clearfix'}
})
export class RepositoryMenuBar {
    public repository = input.required<Repository>();
    public active     = input.required<'reviews' | 'revisions' | 'branches'>();
}
