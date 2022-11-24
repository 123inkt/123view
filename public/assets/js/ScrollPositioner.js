import Controller from './Controller.js';

export default class ScrollPositioner extends Controller {

    connect() {
        const hash = window.location.hash;
        if (hash !== '') {
            this.handleHash(hash);
        }
    }

    handleHash(hash) {
        let target = this.findTarget(hash);
        if (target !== null) {
            target.scrollIntoView({block: 'center'});
        }
    }

    findTarget(hash) {
        let matches = hash.match(/^#focus:comment:(\d+)$/);
        if (matches !== null) {
            return this.el.querySelector(`[data-comment="${matches[1]}"]`)
        }

        matches = hash.match(/^#focus:reply:(\d+)$/);
        if (matches !== null) {
            return this.el.querySelector(`[data-reply="${matches[1]}"]`)
        }

        matches = hash.match(/^#focus:line:(\d+)$/);
        if (matches !== null) {
            return this.el.querySelector(`[data-line="${matches[1]}"]`)
        }

        return null;
    }
}
