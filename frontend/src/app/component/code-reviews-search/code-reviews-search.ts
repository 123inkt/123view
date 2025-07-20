import { Component } from '@angular/core';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
  selector: 'app-code-reviews-search',
    imports: [
        TranslatePipe
    ],
  templateUrl: './code-reviews-search.html',
  styleUrl: './code-reviews-search.scss'
})
export class CodeReviewsSearch {

}
