import {Controller} from '@hotwired/stimulus';
import Assert from '../lib/Assert';

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

    public expandUp(): void {
        this.expand('up');
    }

    public expandDown(): void {
        this.expand('down');
    }

    private expand(direction: 'up' | 'down'): void {
        // if (this.expanded) {
        //     return;
        // }

        // todo this.expanded = true
        const lines       = this.getExpandableLines();
        const expandCount = lines.length > 130 ? 100 : lines.length;
        const expandStart = direction === 'down' ? lines.length - expandCount : 0;
        const expandEnd   = direction === 'down' ? lines.length : expandCount;

        console.log('direction', direction);
        console.log('lineCount', lines.length);
        console.log('expandStart', expandStart);
        console.log('expandEnd', expandEnd);

        // show hidden lines between start and end
        lines.forEach((line, index) => {
            if (expandStart <= index && index < expandEnd) {
                line.classList.remove('diff-file__diff-line-hidden');
            }
        });

        if (expandCount === lines.length) {
            // and remove the expander if all lines are shown
            this.element.remove();
        } else if (direction === 'down') {
            // move expander to the first visible line
            Assert.notUndefined(lines[expandStart]).insertAdjacentElement('beforebegin', this.element);
        }

        // notify other line expanders on the page
        // TODO document.dispatchEvent(new CustomEvent('line-expander', {detail: this.element.dataset.lineNumber}));
    }

    private expandOnEvent(event: Event): void {
        if (this.element.dataset.lineNumber === (event as CustomEvent).detail) {
            // TODO
            this.expand('up');
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
