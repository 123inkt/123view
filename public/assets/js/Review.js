import Controller from './Controller.js';

export default class Review extends Controller {

    connect() {
        this.listen('click', 'line-add-comment', this.addComment.bind(this));
        this.listen('click', 'reply-to-comment', this.replyToComment.bind(this));
    }

    addComment(target) {
        const location = new URL(window.location.href);
        location.searchParams.set('filePath', this.role('revision-file').dataset.file);
        location.searchParams.set('addComment', target.dataset.line + ':' + target.dataset.lineOffset + ':' + target.dataset.lineAfter);
        location.searchParams.delete('replyComment');

        window.location = location.toString();
    }

    replyToComment(target) {
        const location = new URL(window.location.href);
        location.searchParams.delete('addComment');
        location.searchParams.set('replyComment', target.dataset.replyTo);

        window.location = location.toString();
    }
}
