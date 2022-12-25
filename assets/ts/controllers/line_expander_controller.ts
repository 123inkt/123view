import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public expand(): void {
        // show all hidden lines above this collapsed block
        for (let el = this.element.previousSibling; el !== null; el = el.previousSibling) {
            if (el.nodeType !==  Node.ELEMENT_NODE) {
                continue;
            }
            if ((<HTMLElement>el).classList.contains('diff-file__diff-line-hidden') === false) {
                break;
            }
            (<HTMLElement>el).classList.remove('diff-file__diff-line-hidden');
        }
        // and remove the expander
        this.element.remove();
    }
}
