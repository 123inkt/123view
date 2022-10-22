import Controller from './Controller.js';

export default class Comment extends Controller {
    connect() {
       this.role('comment-textarea').focus();
       this.listen('keyup', 'comment-textarea', this.commentKeyListener.bind(this));
       this.listen('click', 'cancel-comment', this.cancelComment.bind(this));
    }

    commentKeyListener(target, event) {
        // ctrl + enter should submit the form
        if (event.key === 'Enter' && event.ctrlKey) {
            target.closest('form').submit();
        }
    }

    cancelComment()  {
        const location = new URL(window.location.href);
        location.searchParams.delete('addComment');
        location.searchParams.delete('editComment');
        location.searchParams.delete('replyComment');
        location.searchParams.delete('editReply');
        window.location = location.toString();
    }
}
