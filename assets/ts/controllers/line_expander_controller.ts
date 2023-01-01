import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public expand(): void {
        // show all hidden lines above this collapsed block
        for (let el = this.element.previousElementSibling; el !== null; el = el.previousElementSibling) {
            if (el.matches('[data-role~="diff-line"]') === false) {
                continue;
            }
            if (el.classList.contains('diff-file__diff-line-hidden') === false) {
                break;
            }
            el.classList.remove('diff-file__diff-line-hidden');
        }
        // and remove the expander
        this.element.remove();
    }
}
