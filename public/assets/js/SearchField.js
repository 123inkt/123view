import Controller from './Controller.js';

export default class SearchField extends Controller {
    connect() {
        // focus field and place cursor at the end
        this.el.focus();
        this.el.selectionStart = this.el.selectionEnd = this.el.value.length;
    }
}
