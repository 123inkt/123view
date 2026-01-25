import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLFormElement> {
    public connect(): void {
        this.element.addEventListener('change', () => this.element.submit());
    }
}
