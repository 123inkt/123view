import {Controller} from '@hotwired/stimulus';
import DataSet from '../lib/DataSet';
import Errors from '../lib/Errors';
import Events from '../lib/Events';
import CommentService from '../service/CommentService';

export default class extends Controller<HTMLElement> {
    public static values = {id: Number, url: String};

    private readonly commentService = new CommentService();
    private declare idValue: number;
    private declare urlValue: string;

    public commentUpdated(event: CustomEvent): void {
        Events.stop(event);
        if (parseInt(event.detail) !== this.idValue) {
            return;
        }
        this.updateCommentThread();
    }

    public editComment(event: Event): void {
        Events.stop(event);
        this.updateCommentThread('edit-comment:' + this.idValue);
    }

    public deleteComment(event: Event): void {
        Events.stop(event);

        const message = DataSet.string(<HTMLElement>event.currentTarget, 'confirmMessage');
        if (confirm(message) === false) {
            return;
        }

        this.commentService
            .deleteComment(this.urlValue)
            .then(() => this.element.remove())
            .catch(Errors.catch);
    }

    public replyToComment(event: Event): void {
        Events.stop(event);
        this.updateCommentThread('add-reply:' + this.idValue);
    }

    public editReplyComment(event: Event): void {
        Events.stop(event);
        this.updateCommentThread('edit-reply:' + (<HTMLElement>event.currentTarget).dataset.replyId);
    }

    public deleteReplyComment(event: Event): void {
        Events.stop(event);
        const target  = <HTMLElement>event.currentTarget;
        const message = DataSet.string(target, 'confirmMessage');
        if (confirm(message) === false) {
            return;
        }

        this.commentService
            .deleteCommentReply(DataSet.string(target, 'url'))
            .then(() => this.updateCommentThread())
            .catch(Errors.catch);
    }

    public resolveComment(event: Event): void {
        Events.stop(event);
        const target  = <HTMLElement>event.currentTarget;

        this.commentService
            .changeCommentState(DataSet.string(target, 'url'), DataSet.string(target, 'state'))
            .then(() => this.updateCommentThread())
            .catch(Errors.catch);
    }

    private updateCommentThread(action?: string): void {
        this.commentService
            .getCommentThread(this.urlValue, action)
            .then(el => this.element.replaceWith(el))
            .catch(Errors.catch);
    }
}
