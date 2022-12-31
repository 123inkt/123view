import {Controller} from '@hotwired/stimulus';
import Events from '../lib/Events';
import Function from '../lib/Function';
import CommentService from '../service/CommentService';

export default class extends Controller<HTMLElement> {
    public static values = {id: Number, url: String};

    private readonly commentService = new CommentService();
    private declare idValue: number;
    private declare urlValue: string;

    public editComment(event: Event): void {
        Events.stop(event);
        this.commentService
            .getCommentThread(this.urlValue, 'edit-comment:' + this.idValue)
            .then(el => this.element.replaceWith(el))
            .catch(Function.empty);
    }

    public deleteComment(event: Event): void {
        Events.stop(event);

        const message = (<HTMLElement>event.currentTarget).dataset.confirmMessage;
        if (message !== null && confirm(message) === false) {
            return;
        }

        this.commentService
            .deleteComment(this.urlValue)
            .then(() => this.element.remove())
            .catch(Function.empty);
    }
}
