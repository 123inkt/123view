import {Controller} from '@hotwired/stimulus';
import DataSet from '../lib/DataSet';
import Elements from '../lib/Elements';
import Function from '../lib/Function';
import CommentService from '../service/CommentService';

export default class extends Controller {
    public static targets           = ['revisionFile', 'commentForm'];
    public static values            = {addCommentUrl: String};
    private readonly commentService = new CommentService();
    private declare revisionFileTarget: HTMLElement;
    private declare commentFormTargets: HTMLElement[];
    private declare addCommentUrlValue: string;

    public addComment(event: Event): void {
        const line = Elements.closestRole(<HTMLElement>event.target, 'diff-line');
        this.commentService
            .getAddCommentForm(
                this.addCommentUrlValue,
                DataSet.string(this.revisionFileTarget, 'file'),
                DataSet.int(line, 'line'),
                DataSet.int(line, 'lineOffset'),
                DataSet.int(line, 'lineAfter')
            )
            .then(form => {
                this.commentFormTargets.forEach(el => el.remove());
                Elements.siblingRole(line, 'add-comment-inserter').after(form)
            })
            .catch(Function.empty);
    }

    // public editComment(event: Event): void {
    //     const target   = this.getTarget(event);
    //     const location = new URL(window.location.href);
    //     location.searchParams.set('filePath', this.getFilePath());
    //     location.searchParams.set('action', 'edit-comment:' + target.dataset.commentId);
    //     (window as Window).location = location.toString();
    // }

    public deleteComment(event: Event): void {
        const target = this.getTarget(event);
        if (confirm(target.dataset.confirmMessage)) {
            target.closest('form')?.submit();
        }
    }

    public replyToComment(event: Event): void {
        const target   = this.getTarget(event);
        const location = new URL(window.location.href);
        location.searchParams.set('action', 'add-reply:' + target.dataset.replyTo);
        (window as Window).location = location.toString();
    }

    public editReply(event: Event): void {
        const target   = this.getTarget(event);
        const location = new URL(window.location.href);
        location.searchParams.set('filePath', this.getFilePath());
        location.searchParams.set('action', 'edit-reply:' + target.dataset.replyId);
        (window as Window).location = location.toString();
    }

    public deleteReply(event: Event): void {
        const target = this.getTarget(event);
        if (confirm(target.dataset.confirmMessage)) {
            target.closest('form')?.submit();
        }
    }

    private getTarget(event: Event): HTMLElement {
        return <HTMLElement>((<HTMLElement>event.target).closest('[data-action]'));
    }

    private getFilePath(): string {
        return this.revisionFileTarget.dataset.file ?? '';
    }
}
