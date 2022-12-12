import Controller from './Controller.js';

export default class CheckboxSelectAll extends Controller {
    connect() {
        this.listen('change', 'checkbox-select-all', this.toggleAll.bind(this));
    }

    toggleAll(target) {
        this.el.querySelectorAll('[data-role~="checkbox-select-target"]').forEach(el => el.checked = target.checked);
    }
}
