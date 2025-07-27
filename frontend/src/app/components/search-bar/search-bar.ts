import {Component, input, output} from '@angular/core';
import {FormsModule} from '@angular/forms';
import SearchModel from '@model/forms/SearchModel';

@Component({
    selector: 'app-search-bar',
    imports: [FormsModule],
    templateUrl: './search-bar.html',
    host: {
        class: 'input-group'
    }
})
export class SearchBar {
    public searchModel  = input.required<SearchModel>();
    public placeholder  = input<string | null>(null);
    public searchAction = output<string>();

    public onSearch(): void {
        this.searchAction.emit(this.searchModel().search);
    }
}
