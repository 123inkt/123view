import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets = ['template']
    public static values = {userId: Number, reviewId: Number};

    private templateTarget?: HTMLTemplateElement;
    private userIdValue?: number;
    private reviewIdValue?: number;

    private events = [
        'review-accepted',
        'review-rejected',
        'review-closed',
        'comment-resolved',
        'comment-added',
        'comment-removed',
        'comment-reply-added',
    ];

    public connect(): void {
        document.addEventListener('notification', this.handleNotification.bind(this));
    }

    private handleNotification(event: Event): void {
        const userId   = this.userIdValue ?? 0;
        const reviewId = this.reviewIdValue ?? 0;
        const data     = (<CustomEvent>event).detail;

        // notification for different review
        if (data.reviewId !== reviewId) {
            return;
        }

        // skip notifications from me
        if (data.userId === userId) {
            return;
        }

        // only listen for specific events
        if (this.events.includes(data.eventName) === false) {
            return;
        }

        this.element.appendChild(this.createItem(data.message));
    }

    private createItem(message: string) {
        const clone = <HTMLElement>this.templateTarget!.content.cloneNode(true);

        clone.querySelector('[data-role=item]')!.innerHTML = message;
        return clone;
    }
}
