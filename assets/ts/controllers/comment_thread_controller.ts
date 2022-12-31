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
}
