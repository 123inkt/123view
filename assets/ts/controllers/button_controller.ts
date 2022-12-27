import {Controller} from '@hotwired/stimulus';
import ElementFactory from '../lib/ElementFactory';

export default class extends Controller<HTMLButtonElement> {
    public connect(): void {
        this.element.addEventListener('click', this.load.bind(this));
    }

    private load(): void {
        if (this.element.type !== 'submit' || this.element.closest('form')?.checkValidity() === false) {
            return;
        }

        const icon = ElementFactory.createElement('<span class="spinner-border spinner-border-sm me-1"/>');
        this.element.disabled = true;
        this.element.insertBefore(icon, this.element.firstChild);
    }
}
