import {Controller} from '@hotwired/stimulus';
import ExpandLineEvent from '../ExpandLineEvent';
import Assert from '../lib/Assert';
import Elements from '../lib/Elements';

export default class extends Controller<HTMLElement> {
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

    public expandUp(): void {
        this.expand('up');
    }

    public expandDown(): void {
        this.expand('down');
    }

    private expand(direction: 'up' | 'down'): void {
        const lines       = this.getExpandableLines();
        const expandCount = lines.length > 50 ? 30 : lines.length;
        const expandStart = direction === 'down' ? lines.length - expandCount : 0;
        const expandEnd   = direction === 'down' ? lines.length : expandCount;
        const offsetTop   = this.element.offsetTop;

        // show hidden lines between start and end
        lines.forEach((line, index) => {
            if (expandStart <= index && index < expandEnd) {
                line.classList.remove('diff-file__diff-line-hidden');
            }
        });

        // ensure this is still at the same position within the viewport
        if (direction === 'up') {
            Assert.notNull(Elements.getScrollParent(this.element)).scrollTop += this.element.offsetTop - offsetTop;
        }

        // update line count counter
        Assert.notNull(this.element.querySelector<HTMLElement>('[data-role=line-count]')).innerText = String(lines.length - expandCount);

        if (expandCount === lines.length) {
            // and remove the expander if all lines are shown
            this.element.remove();
        } else if (direction === 'down') {
            // move expander to the first visible line
            Assert.notUndefined(lines[expandStart]).insertAdjacentElement('beforebegin', this.element);
        }

        // notify other line expanders on the page
        document.dispatchEvent(new ExpandLineEvent(Assert.notUndefined(this.element.dataset.lineNumber), direction, this));
    }

    private expandOnEvent(event: Event): void {
        const lineEvent = event as ExpandLineEvent;
        if (this.element.dataset.lineNumber === lineEvent.lineNumber && lineEvent.source !== this) {
            this.expand(lineEvent.direction);
        }
    }

    /**
     * Gather all invisible lines above this element
     */
    private getExpandableLines(): HTMLElement[] {
        const elements: HTMLElement[] = [];

        // show all hidden lines above this collapsed block
        for (let el = this.element.previousElementSibling; el !== null; el = el.previousElementSibling) {
            if (el.matches('[data-role~="diff-line"]') === false) {
                continue;
            }
            if (el.classList.contains('diff-file__diff-line-hidden') === false) {
                break;
            }
            elements.push(el as HTMLElement);
        }

        return elements.reverse();
    }
}
