import {Controller} from '@hotwired/stimulus';
import ElementFactory from '../lib/ElementFactory';

export default class extends Controller {
    public static targets = ['filterList', 'filterTemplate'];
    public static values  = {count: Number};

    private filterListTarget?: HTMLElement;
    private filterTemplateTarget?: HTMLTemplateElement;
    private countValue?: number;

    public addFilter(): void {
        // maximum filter reached
        if (this.filterListTarget!.children.length >= 10) {
            return;
        }

        // get the template
        const template = this.filterTemplateTarget!.innerHTML;

        // create new element from template
        const element = ElementFactory.createElement(template.replace(/__name__/g, String(this.countValue!++)));

        // append to the end
        this.filterListTarget!.appendChild(element);
    }

    public deleteFilter(event: Event): void {
        (<HTMLElement>event.target).closest('[data-role="filter"]')?.remove();
    }
}
