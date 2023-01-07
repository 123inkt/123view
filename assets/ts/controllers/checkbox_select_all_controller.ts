import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets            = ['toggle'];
    declare toggleTargets: HTMLInputElement[];

    public toggleAll(event: Event): void {
        const dataRole = (<HTMLInputElement>event.currentTarget).dataset.role;

        if (dataRole === undefined) {
            this.toggleTargets.forEach(el => el.checked = (<HTMLInputElement>event.target).checked);
        } else {
            this.element
                .querySelectorAll<HTMLInputElement>(`[data-role~="${dataRole}"]`)
                .forEach(el => el.checked = (<HTMLInputElement>event.target).checked);
        }
    }
}
