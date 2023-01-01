import {Controller} from '@hotwired/stimulus';
import Elements from '../lib/Elements';
import Events from '../lib/Events';

export default class extends Controller {
    public static targets = ['recipientList', 'recipientTemplate'];
    public static values  = {count: Number};

    declare recipientListTarget: HTMLElement;
    declare recipientTemplateTarget: HTMLTemplateElement;
    declare countValue: number;

    public addRecipient(event: Event): void {
        Events.stop(event);

        // maximum recipient reached
        if (this.recipientListTarget.children.length >= 10) {
            return;
        }

        // get the template
        const template = this.recipientTemplateTarget.innerHTML;

        // create new element from template
        const element = Elements.create(template.replace(/__name__/g, String(this.countValue++)));

        // append to the end
        this.recipientListTarget.appendChild(element);
    }

    public deleteRecipient(event: Event) : void{
        Events.stop(event);
        (<HTMLElement>event.target).closest('[data-role="recipient"]')?.remove();
    }
}
