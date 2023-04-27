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
            this.showAndPosition();
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

    private showAndPosition(): void {
        // set visible and reset position
        this.dropdownTarget.style.left    = '0px';
        this.dropdownTarget.style.display = 'block';

        // gather widths of button and dropdown
        const buttonWidth   = this.buttonTarget.offsetWidth;
        const dropdownWidth = this.dropdownTarget.offsetWidth;
        const dropdownLeft  = this.dropdownTarget.getBoundingClientRect().left;

        // dropdown is outside the viewport, shift to the left
        if (dropdownLeft + dropdownWidth > window.innerWidth) {
            this.dropdownTarget.style.left = `-${dropdownWidth - buttonWidth}px`;
        }
    }
}
