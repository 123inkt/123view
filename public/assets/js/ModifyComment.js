import Controller from './Controller.js';

export default class Comment extends Controller {

    connect() {
       this.role('comment-textarea').focus();
       this.listen('click', 'cancel-comment', this.cancelComment.bind(this));
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
