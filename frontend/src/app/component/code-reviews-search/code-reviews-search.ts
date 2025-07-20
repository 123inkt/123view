import {Component, model} from '@angular/core';
import {FormsModule} from '@angular/forms';
import ReviewsSearchModel from '@model/forms/ReviewsSearchModel';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'app-code-reviews-search',
    imports: [TranslatePipe, FormsModule],
    templateUrl: './code-reviews-search.html',
    styleUrl: './code-reviews-search.scss'
})
export class CodeReviewsSearch {
    public searchModel = model.required<ReviewsSearchModel>({alias: 'search'});
}
