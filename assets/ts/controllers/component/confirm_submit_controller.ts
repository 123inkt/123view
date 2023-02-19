import {Controller} from '@hotwired/stimulus';
import DataSet from '../../lib/DataSet';
import Events from '../../lib/Events';

export default class extends Controller<HTMLFormElement> {
    public connect(): void {
        this.element.addEventListener('submit', this.onSubmit.bind(this));
    }

    private onSubmit(event: Event): void {
        if (confirm(DataSet.string(this.element, 'confirmMessage')) === false) {
            Events.stop(event);
        }
    }
}
