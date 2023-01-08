import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets = ['toggle'];
    private readonly declare toggleTargets: HTMLInputElement[];

    public toggleAll(event: Event): void {
        const target = event.currentTarget as HTMLInputElement;
        const role   = target.dataset.role;

        if (role === undefined) {
            this.toggleTargets.forEach(el => el.checked = target.checked);
        } else {
            this.element
                .querySelectorAll<HTMLInputElement>(`[data-role~="${role}"]`)
                .forEach(el => el.checked = target.checked);
        }
    }
}
