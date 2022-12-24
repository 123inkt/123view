import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public connect(): void {
        const element = (<HTMLFormElement>this.element);
        element.addEventListener('change', () => element.submit());
    }
}
