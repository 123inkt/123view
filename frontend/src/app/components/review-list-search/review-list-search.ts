import {Component, input, output} from '@angular/core';
import {FormsModule} from '@angular/forms';
import ReviewsSearchModel from '@model/forms/ReviewsSearchModel';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'app-review-list-search',
    imports: [TranslatePipe, FormsModule],
    templateUrl: './review-list-search.html',
    styleUrl: './review-list-search.scss'
})
export class ReviewListSearch {
    public searchModel  = input.required<ReviewsSearchModel>();
    public searchAction = output<void>();

    public onSearch(): void {
        this.searchAction.emit();
    }
}
