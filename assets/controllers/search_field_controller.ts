import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const el = <HTMLInputElement>this.element;
        // focus field and place cursor at the end
        el.focus();
        el.selectionStart = el.selectionEnd = el.value.length;
    }
}
