import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLElement> {
    private expanded: boolean = false;

    public connect(): void {
        this.expandOnEvent = this.expandOnEvent.bind(this);
        document.addEventListener('line-expander', this.expandOnEvent);
    }

    public disconnect(): void {
        document.removeEventListener('line-expander', this.expandOnEvent);
    }

    public getLineNumber(): string | undefined {
        return this.element.dataset.lineNumber;
    }

    public expand(): void {
        if (this.expanded) {
            return;
        }
        this.expanded = true;

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

        // notify other line expanders on the page
        document.dispatchEvent(new CustomEvent('line-expander', {detail: this.element.dataset.lineNumber}));
    }

    private expandOnEvent(event: Event): void {
        if (this.element.dataset.lineNumber === (event as CustomEvent).detail) {
            this.expand();
        }
    }
}
