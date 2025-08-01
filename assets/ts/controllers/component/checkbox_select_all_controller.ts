import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets = ['toggle'];

    public toggleAll(event: Event): void {
        const target    = event.currentTarget as HTMLInputElement;
        const role      = target.dataset.forRole;
        const revisions = this.getRevisions();

        revisions.forEach(el => el.querySelector<HTMLInputElement>(`[data-role~="${role}"]`)!.checked = target.checked);
    }

    private getRevisions(): HTMLElement[] {
        return Array.from(this.element.querySelectorAll<HTMLElement>(`[data-role~="revision"]`))
            .filter(el => el.checkVisibility());
    }
}
