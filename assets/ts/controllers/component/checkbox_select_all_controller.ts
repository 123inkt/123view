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

    public search(event: Event): void {
        const target        = event.currentTarget as HTMLInputElement;
        const search        = target.value.toLowerCase();
        const role          = target.dataset.toggleRole;
        const toggleTargets = role === undefined
            ? this.toggleTargets
            : this.element.querySelectorAll(`[data-role~="${role}"]`);

        toggleTargets.forEach(el => {
            if (el.querySelector(`[data-role~="title"]`)?.title.toLowerCase().includes(search)) {
                el.classList.remove('d-none');
            } else {
                el.classList.add('d-none');
            }
        });
    }
}
