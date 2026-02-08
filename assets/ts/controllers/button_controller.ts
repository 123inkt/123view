import {Controller} from '@hotwired/stimulus';
import Elements from '../lib/Elements';

export default class extends Controller<HTMLButtonElement> {
    private observer?: MutationObserver;
    private icon?: HTMLElement;

    public connect(): void {
        this.observer = new MutationObserver(this.onAttributeChange.bind(this));
        this.element.addEventListener('click', this.load.bind(this));
    }

    public disconnect(): void {
        this.observer?.disconnect();
        this.observer = undefined;
    }

    private onAttributeChange(): void {
        if (this.element.disabled === false) {
            this.icon?.remove();
            this.icon = undefined;
            this.observer?.disconnect();
        }
    }

    private load(): void {
        if (this.element.type === 'submit' && this.element.closest('form')?.checkValidity() === false) {
            return;
        }

        this.icon = Elements.create('<span class="spinner-border spinner-border-sm me-1"/>');
        this.element.insertBefore(this.icon, this.element.firstChild);

        // delay disable slightly to avoid blocking the submit
        window.setTimeout(() => this.element.disabled = true, 1);

        // listen for attribute changes
        this.observer?.disconnect();
        this.observer?.observe(this.element, {attributes: true});
    }
}
