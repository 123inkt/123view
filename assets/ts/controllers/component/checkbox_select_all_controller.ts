import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets = ['toggle'];

    public toggleAll(event: Event): void {
        const target    = event.currentTarget as HTMLInputElement;
        const role      = target.dataset.forRole;
        const search    = this.element.querySelector<HTMLInputElement>('[data-role~="search"]')?.value.toLowerCase() ?? '';
        const revisions = this.getRevisions();

        if (search === '' || search === undefined) {
            revisions.forEach(el => el.querySelector<HTMLInputElement>(`[data-role~="${role}"]`)!.checked = target.checked);
        } else {
            revisions.forEach(el => el.querySelector<HTMLInputElement>(`[data-role~="${role}"]`)!.checked = this.matchesSearch(el.closest('[data-role~="revision"]'), search) && target.checked);
        }
    }

    private getRevisions(): HTMLElement[] {
        return Array.from(this.element.querySelectorAll<HTMLElement>(`[data-role~="revision"]`));
    }

    private matchesSearch(el: HTMLElement | null, search: string | undefined): boolean {
        if (el === null || search === undefined || search === '') {
            return true;
        }

        return el.dataset.title?.toLowerCase().includes(search) === true
            || el.dataset.revision?.toLowerCase().includes(search) === true;
    }
}
