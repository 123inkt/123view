import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const element = (<HTMLFormElement>this.element);
        element.addEventListener('change', () => element.submit());
    }
}
