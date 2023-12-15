import {Controller} from '@hotwired/stimulus';
import DataSet from '../lib/DataSet';
import Elements from '../lib/Elements';
import Errors from '../lib/Errors';
import CommentService from '../service/CommentService';

export default class extends Controller {
    public static targets           = ['revisionFile', 'commentForm'];
    public static values            = {addCommentUrl: String};
    private readonly commentService = new CommentService();
    private readonly declare revisionFileTarget: HTMLElement;
    private readonly declare commentFormTargets: HTMLElement[];
    private readonly declare addCommentUrlValue: string;

    public addComment(event: Event): void {
        const line = Elements.closestRole(event.target as HTMLElement, 'diff-line');
        this.commentService
            .getAddCommentForm(
                this.addCommentUrlValue,
                DataSet.string(this.revisionFileTarget, 'oldPath'),
                DataSet.string(this.revisionFileTarget, 'newPath'),
                DataSet.int(line, 'line'),
                DataSet.int(line, 'lineOffset'),
                DataSet.int(line, 'lineAfter'),
                DataSet.string(line, 'lineState')
            )
            .then(form => {
                this.commentFormTargets.forEach(el => el.remove());
                Elements.siblingRole(line, 'add-comment-inserter').after(form);
            })
            .catch(Errors.catch);
    }
}
