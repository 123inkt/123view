import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets = ['revisionFile'];
    private revisionFileTarget?: HTMLElement;

    public addComment(event: Event): void {
        const target   = this.getTarget(event);
        const location = new URL(window.location.href);
        location.searchParams.set('filePath', this.getFilePath());
        location.searchParams.set('action', 'add-comment:' + target.dataset.line + ':' + target.dataset.lineOffset + ':' + target.dataset.lineAfter);
        (window as Window).location = location.toString();
    }

    public editComment(event: Event): void {
        const target   = this.getTarget(event);
        const location = new URL(window.location.href);
        location.searchParams.set('filePath', this.getFilePath());
        location.searchParams.set('action', 'edit-comment:' + target.dataset.commentId);
        (window as Window).location = location.toString();
    }

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
        return this.revisionFileTarget?.dataset.file ?? '';
    }
}
