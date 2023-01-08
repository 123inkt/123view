import {Controller} from '@hotwired/stimulus';
import Elements from '../lib/Elements';
import Events from '../lib/Events';

export default class extends Controller {
    public static targets = ['filterList', 'filterTemplate'];
    public static values  = {count: Number};

    private readonly declare filterListTarget: HTMLElement;
    private readonly declare filterTemplateTarget: HTMLTemplateElement;
    private declare countValue: number;

    public addFilter(event: Event): void {
        Events.stop(event);

        // maximum filter reached
        if (this.filterListTarget.children.length >= 10) {
            return;
        }

        // get the template
        const template = this.filterTemplateTarget.innerHTML;

        // create new element from template
        const element = Elements.create(template.replace(/__name__/g, String(this.countValue++)));

        // append to the end
        this.filterListTarget.appendChild(element);
    }

    public deleteFilter(event: Event): void {
        Events.stop(event);
        (event.target as HTMLElement).closest('[data-role="filter"]')?.remove();
    }
}
