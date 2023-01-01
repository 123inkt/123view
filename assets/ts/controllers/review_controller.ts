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
}
