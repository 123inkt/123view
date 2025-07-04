import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets = ['toggle'];

    public search(event: Event): void {
        const target    = event.currentTarget as HTMLInputElement;
        const search    = target.value.toLowerCase();
        const revisions = this.getRevisions();

        console.log({target, search, revisions, element: this.element});

        if (search === '') {
            target.closest('.review-revision')?.classList.remove('search-active');

            this.updateToggleCheckboxes(revisions, search);

            return;
        }

        target.closest('.review-revision')?.classList.add('search-active');
        revisions.forEach(el => {
            if (this.matchesSearch(el, search)) {
                el.classList.add('search-match');
            } else {
                el.classList.remove('search-match');
            }
        });
        this.updateToggleCheckboxes(revisions, search);
    }

    private getRevisions(): HTMLElement[] {
        return Array.from(this.element.parentElement!.querySelectorAll<HTMLElement>(`[data-role~="revision"]`));
    }

    private updateToggleCheckboxes(revisions: HTMLElement[], search: string): void {
        this.updateToggle(revisions, search, 'detach', 'detach-toggle');
        this.updateToggle(revisions, search, 'visibility', 'visibility-toggle');
    }

    private updateToggle(revisions: HTMLElement[], search: string, checkboxRole: string, toggleRole: string): void {
        const toggleCheckbox = this.element.querySelector<HTMLInputElement>(`[data-role~="${toggleRole}"]`);
        if (toggleCheckbox === null) {
            return;
        }
        const searchMatchRevisions = revisions.filter(el => this.matchesSearch(el, search));
        toggleCheckbox.checked     = searchMatchRevisions
            .map(el => el.querySelector<HTMLInputElement>(`[data-role~="${checkboxRole}"]`))
            .filter(el => el !== null)
            .every(el => el.checked) === false;
    }

    private matchesSearch(el: HTMLElement | null, search: string | undefined): boolean {
        if (el === null || search === undefined || search === '') {
            return true;
        }

        return el.dataset.title?.toLowerCase().includes(search) === true
            || el.dataset.revision?.toLowerCase().includes(search) === true;
    }
}
