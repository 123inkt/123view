import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets            = ['toggle'];
    private toggleTargets?: HTMLInputElement[];

    public toggleAll(event: Event): void {
        this.toggleTargets?.forEach(el => el.checked = (<HTMLInputElement>event.target).checked)
    }
}
