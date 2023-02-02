import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets = ['button', 'dropdown'];
    private readonly declare buttonTarget: HTMLElement;
    private readonly declare dropdownTarget: HTMLElement;

    public connect(): void {
        this.hide = this.hide.bind(this);
    }

    public show(): void {
        window.setTimeout(() => {
            this.dropdownTarget.style.display = 'block';
            document.addEventListener('click', this.hide);
        }, 1);
    }

    private hide(event: Event): void {
        if (event.target === null || this.dropdownTarget === event.target || this.dropdownTarget.contains(event.target as Node)) {
            return;
        }
        this.dropdownTarget.style.display = '';
        document.removeEventListener('click', this.hide);
    }
}
