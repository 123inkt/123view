import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static targets = ['template'];
    public static values = {userId: Number, reviewId: Number};

    declare templateTarget: HTMLTemplateElement;
    declare userIdValue: number;
    declare reviewIdValue: number;

    private readonly events = [
        'review-accepted',
        'review-rejected',
        'review-closed',
        'comment-resolved',
        'comment-added',
        'comment-removed',
        'comment-reply-added'
    ];

    public connect(): void {
        document.addEventListener('notification', this.handleNotification.bind(this));
    }

    private handleNotification(event: Event): void {
        const userId   = this.userIdValue;
        const reviewId = this.reviewIdValue;
        const data     = (event as CustomEvent).detail;

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

    private createItem(message: string): HTMLElement {
        const clone = this.templateTarget.content.cloneNode(true) as HTMLElement;

        (clone.querySelector('[data-role=item]') as HTMLElement).innerHTML = message;
        return clone;
    }
}
