import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public connect(): void {
        const hash = window.location.hash;
        if (hash !== '') {
            this.handleHash(hash);
        }
    }

    private handleHash(hash: string): void {
        let target = this.findTarget(hash);
        if (target !== null) {
            target.scrollIntoView({block: 'center'});
        }
    }

    private findTarget(hash: string): Element | null {
        let matches = hash.match(/^#focus:comment:(\d+)$/);
        if (matches !== null) {
            return this.element.querySelector(`[data-comment="${matches[1]}"]`)
        }

        matches = hash.match(/^#focus:reply:(\d+)$/);
        if (matches !== null) {
            return this.element.querySelector(`[data-reply="${matches[1]}"]`)
        }

        matches = hash.match(/^#focus:line:(\d+)$/);
        if (matches !== null) {
            return this.element.querySelector(`[data-line="${matches[1]}"]`)
        }

        return null;
    }
}
