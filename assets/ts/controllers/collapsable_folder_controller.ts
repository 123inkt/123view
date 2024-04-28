import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLFormElement> {
    public toggle(): void {
        this.element.classList.toggle('collapsed');
    }
}
