import Controller from './Controller.js';

export default class Review extends Controller {
    connect() {
        this.listen('click', 'line-add-comment', this.addComment.bind(this));
        this.listen('click', 'reply-to-comment', this.replyToComment.bind(this));
        this.listen('click', 'edit-comment', this.editComment.bind(this));
        this.listen('click', 'delete-comment', this.deleteComment.bind(this));
        this.listen('click', 'edit-reply', this.editReply.bind(this));
        this.listen('click', 'delete-reply', this.deleteReply.bind(this));
    }

    addComment(target) {
        const location = this.filterUrl(new URL(window.location.href));
        location.searchParams.set('filePath', this.role('revision-file').dataset.file);
        location.searchParams.set('addComment', target.dataset.line + ':' + target.dataset.lineOffset + ':' + target.dataset.lineAfter);
        window.location = location.toString();
    }

    editComment(target) {
        const location = this.filterUrl(new URL(window.location.href));
        location.searchParams.set('filePath', this.role('revision-file').dataset.file);
        location.searchParams.set('editComment', target.dataset.commentId);
        window.location = location.toString();
    }

    deleteComment(target) {
        if (confirm(target.dataset.confirmMessage)) {
            target.closest('form').submit();
        }
    }

    replyToComment(target) {
        const location = this.filterUrl(new URL(window.location.href));
        location.searchParams.set('replyComment', target.dataset.replyTo);
        window.location = location.toString();
    }

    editReply(target) {
        const location = this.filterUrl(new URL(window.location.href));
        location.searchParams.set('filePath', this.role('revision-file').dataset.file);
        location.searchParams.set('editReply', target.dataset.replyId);
        window.location = location.toString();
    }

    deleteReply(target) {
        if (confirm(target.dataset.confirmMessage)) {
            target.closest('form').submit();
        }
    }

    /**
     * Cleanup existing url actions
     */
    filterUrl(url) {
        url.searchParams.delete('addComment');
        url.searchParams.delete('editComment');
        url.searchParams.delete('replyComment');
        url.searchParams.delete('editReply');
        return url;
    }
}
