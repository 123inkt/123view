import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLFormElement> {
    public static targets   = ['icon'];
    private readonly declare iconTarget: HTMLElement;
    private isOpen: boolean = true;

    public toggle(): void {
        if (this.isOpen) {
            this.isOpen = false;
            this.element.classList.toggle('collapsed', true);
            this.iconTarget.classList.remove('bi-folder-fill');
            this.iconTarget.classList.add('bi-folder');
        } else {
            this.isOpen = true;
            this.element.classList.toggle('collapsed', false);
            this.iconTarget.classList.add('bi-folder-fill');
            this.iconTarget.classList.remove('bi-folder');
        }
    }
}
