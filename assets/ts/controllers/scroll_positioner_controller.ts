import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public connect(): void {
        const hash = window.location.hash;
        if (hash !== '') {
            this.handleHash(hash);
        }
    }

    private handleHash(hash: string): void {
        const {target, highlight} = this.findTarget(hash);
        if (target === null) {
            return;
        }
        target.scrollIntoView({behavior: 'smooth', block: 'center'});
        if (highlight) {
            target.classList.add('highlighted');
        }
    }

    private findTarget(hash: string): {target: Element | null, highlight: boolean} {
        let matches = /^#focus:comment:(\d+)$/.exec(hash);
        if (matches !== null) {
            return {target: this.element.querySelector(`[data-comment-focus="${matches[1]}"]`), highlight: true};
        }

        matches = /^#focus:reply:(\d+)$/.exec(hash);
        if (matches !== null) {
            return {target: this.element.querySelector(`[data-reply-id="${matches[1]}"]`), highlight: true};
        }

        matches = /^#focus:line:(\d+)$/.exec(hash);
        if (matches !== null) {
            return {target: this.element.querySelector(`[data-line="${matches[1]}"]`), highlight: false};
        }

        return {target: null, highlight: false};
    }
}
