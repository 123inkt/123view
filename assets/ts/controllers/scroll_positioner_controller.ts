import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public connect(): void {
        const hash = window.location.hash;
        if (hash !== '') {
            this.handleHash(hash);
        }
    }

    private handleHash(hash: string): void {
        const target = this.findTarget(hash);
        if (target !== null) {
            target.scrollIntoView({block: 'center'});
        }
    }

    private findTarget(hash: string): Element | null {
        let matches = /^#focus:comment:(\d+)$/.exec(hash);
        if (matches !== null) {
            return this.element.querySelector(`[data-comment-id="${matches[1]}"]`);
        }

        matches = /^#focus:reply:(\d+)$/.exec(hash);
        if (matches !== null) {
            return this.element.querySelector(`[data-reply-id="${matches[1]}"]`);
        }

        matches = /^#focus:line:(\d+)$/.exec(hash);
        if (matches !== null) {
            return this.element.querySelector(`[data-line="${matches[1]}"]`);
        }

        return null;
    }
}
