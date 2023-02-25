import {Controller} from '@hotwired/stimulus';
import DataSet from '../lib/DataSet';
import Elements from '../lib/Elements';
import Events from '../lib/Events';

export default class extends Controller<HTMLButtonElement> {
    public connect(): void {
        this.element.addEventListener('click', this.load.bind(this));
    }

    private load(event: Event): void {
        const openComments    = DataSet.int(this.element, 'openComments');
        const confirmQuestion = DataSet.string(this.element, 'confirmQuestion');

        if (openComments > 0 && confirm(confirmQuestion) === false) {
            Events.stop(event);
            return;
        }

        const icon = Elements.create('<span class="spinner-border spinner-border-sm me-1"/>');
        this.element.insertBefore(icon, this.element.firstChild);

        // delay disable slightly to avoid blocking the submit
        window.setTimeout(() => this.element.disabled = true, 1);
    }
}
