import {Controller} from '@hotwired/stimulus';
import Assert from '../lib/Assert';
import DataSet from '../lib/DataSet';
import Errors from '../lib/Errors';
import Events from '../lib/Events';
import CommentService from '../service/CommentService';

export default class extends Controller<HTMLElement> {
    public static values = {id: Number, url: String};

    private readonly commentService = new CommentService();
    private readonly declare idValue: number;
    private readonly declare urlValue: string;

    public commentUpdated(event: CustomEvent<string>): void {
        Events.stop(event);
        if (parseInt(event.detail, 10) !== this.idValue) {
            return;
        }
        this.updateCommentThread();
    }

    public editComment(event: Event): void {
        Events.stop(event);
        this.updateCommentThread('edit-comment:' + String(this.idValue));
    }

    public deleteComment(event: Event): void {
        Events.stop(event);

        const message = DataSet.string(event.currentTarget as HTMLElement, 'confirmMessage');
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
        this.updateCommentThread('add-reply:' + String(this.idValue));
    }

    public reactToComment(event: Event): void {
        Events.stop(event);
        const target = event.currentTarget as HTMLElement;
        if (target.hasAttribute('disabled')) {
            return;
        }
        target.setAttribute('disabled', '');
        this.commentService.addCommentReaction(Assert.notUndefined(target.dataset.url), Assert.notUndefined(target.dataset.text))
            .then(() => this.updateCommentThread())
            .catch(Errors.catch)
            .finally(() => target.removeAttribute('disabled'));
    }

    public editReplyComment(event: Event): void {
        Events.stop(event);
        this.updateCommentThread('edit-reply:' + String((event.currentTarget as HTMLElement).dataset.replyId));
    }

    public deleteReplyComment(event: Event): void {
        Events.stop(event);
        const target  = event.currentTarget as HTMLElement;
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
        const target  = event.currentTarget as HTMLElement;

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
