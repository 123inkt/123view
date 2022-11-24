import Controller from './Controller.js';

export default class LineExpander extends Controller {
    connect() {
        this.el.addEventListener('click', this.expand.bind(this));
    }

    expand() {
        // show all hidden lines above this collapsed block
        for (let el = this.el.previousSibling; el !== null; el = el.previousSibling) {
            if (el.classList.contains('diff-file__diff-line-hidden') === false) {
                break;
            }
            el.classList.remove('diff-file__diff-line-hidden');
        }
        // and remove the expander
        this.el.remove();
    }
}
