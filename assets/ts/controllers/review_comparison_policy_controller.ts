import {Controller} from '@hotwired/stimulus';
import DataSet from '../lib/DataSet';

export default class extends Controller<HTMLElement> {
    public connect(): void {
        this.element.addEventListener('change', this.onSelect.bind(this));
    }

    private onSelect(event: Event): void {
        window.location.href = DataSet.string((event.target as HTMLElement), 'url');
    }
}
