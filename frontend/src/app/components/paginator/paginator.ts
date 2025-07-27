import {Component, input, output} from '@angular/core';
import {range} from '@lib/Numbers';
import PaginatorViewModel from '@model/viewmodels/PaginatorViewModel';

@Component({
    selector: 'app-paginator',
    imports: [],
    templateUrl: './paginator.html'
})
export class Paginator {
    public paginator    = input.required<PaginatorViewModel>();
    public pageSelected = output<number>();

    public pages(): number[] {
        const startAt = Math.max(1, this.paginator().page - 10);
        const endAt   = Math.min(this.paginator().lastPage, this.paginator().page + 10);

        return range(startAt, endAt);
    }

    public selectPage(page: number): void {
        this.paginator().page = page;
        this.pageSelected.emit(page);
    }
}
