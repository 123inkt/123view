import Controller from './Controller.js';

export default class FormSubmitter extends Controller {
    connect() {
        this.el.addEventListener('change', () => this.el.submit());
    }
}
