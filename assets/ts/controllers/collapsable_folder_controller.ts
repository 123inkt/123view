import {Controller} from '@hotwired/stimulus';
import axios from 'axios';
import Function from '../lib/Function';

export default class extends Controller<HTMLFormElement> {
    public static readonly values = {reviewId: Number, path: String};
    private readonly declare pathValue: number;
    private readonly declare reviewIdValue: number;

    public toggle(): void {
        const collapsed = this.element.classList.contains('collapsed');
        this.element.classList.toggle('collapsed', !collapsed);

        axios
            .post(
                `/app/reviews/${this.reviewIdValue}/folder-collapse-status`,
                {path: this.pathValue, state: collapsed ? 'expanded': 'collapsed'},
                {headers: {'Content-Type': 'multipart/form-data'}}
            )
            .catch(Function.empty);
    }
}
