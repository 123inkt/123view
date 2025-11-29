import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLInputElement> {
    public connect(): void {
        // focus field and place cursor at the end
        this.element.focus();
        this.element.selectionStart = this.element.value.length;
        this.element.selectionEnd   = this.element.value.length;
    }
}
