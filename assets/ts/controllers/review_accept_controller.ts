import {Controller} from '@hotwired/stimulus';
import Elements from '../lib/Elements';
import Errors from '../lib/Errors';
import Events from '../lib/Events';
import CommentService from '../service/CommentService';
import ReviewNotificationService from '../service/ReviewNotificationService';

export default class extends Controller<HTMLButtonElement> {
    public static targets                = ['count'];
    public static values                 = {reviewId: Number, openComments: Number, confirmQuestion: String, title: String};
    private readonly commentService      = new CommentService();
    private readonly notificationService = new ReviewNotificationService();
    private readonly declare countTarget: HTMLSpanElement;
    private readonly declare reviewIdValue: number;
    private declare openCommentsValue: number;
    private readonly declare confirmQuestionValue: string;
    private readonly declare titleValue: string;

    public connect(): void {
        this.updateUI(this.openCommentsValue);
        this.element.addEventListener('click', this.load.bind(this));
        this.notificationService.subscribe(
            `/review/${String(this.reviewIdValue)}`,
            ['comment-added', 'comment-removed', 'comment-resolved', 'comment-unresolved'],
            this.updateCommentCount.bind(this),
            this.reviewIdValue
        );
    }

    public disconnect(): void {
        this.notificationService.unsubscribe(`/review/${String(this.reviewIdValue)}`);
    }

    private load(event: Event): void {
        if (this.openCommentsValue > 0 && confirm(this.confirmQuestionValue.replace('{count}', String(this.openCommentsValue))) === false) {
            Events.stop(event);
            return;
        }

        const icon = Elements.create('<span class="spinner-border spinner-border-sm me-1"/>');
        this.element.insertBefore(icon, this.element.firstChild);

        // delay disable slightly to avoid blocking the submit
        window.setTimeout(() => this.element.disabled = true, 1);
    }

    private updateCommentCount(): void {
        this.commentService.getCommentCount(this.reviewIdValue)
            .then((result) => {
                this.openCommentsValue = result.open;
                this.updateUI(result.open);
            })
            .catch(Errors.catch);
    }

    private updateUI(openComments: number): void {
        if (openComments > 0) {
            this.element.title         = this.titleValue.replace('{count}', String(openComments));
            this.countTarget.innerText = `(${openComments})`;
        } else {
            this.element.title         = '';
            this.countTarget.innerText = '';
        }
    }
}
