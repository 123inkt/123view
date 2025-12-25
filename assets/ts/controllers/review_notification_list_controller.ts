import {Controller} from '@hotwired/stimulus';
import type MercureEvent from '../entity/MercureEvent';

export default class extends Controller {
    public static targets = ['template'];
    public static values  = {userId: Number, reviewId: Number};

    private readonly declare templateTarget: HTMLTemplateElement;
    private readonly declare userIdValue: number;
    private readonly declare reviewIdValue: number;

    private readonly events = [
        'review-accepted',
        'review-rejected',
        'review-closed',
        'comment-resolved',
        'comment-added',
        'comment-removed',
        'comment-reply-added',
        'request-ai-review',
        'ai-review-completed',
    ];

    public connect(): void {
        document.addEventListener('/review/' + String(this.reviewIdValue), this.handleNotification.bind(this));
    }

    private handleNotification(event: Event): void {
        const data = (event as CustomEvent<MercureEvent>).detail;

        // skip notifications from me
        if (data.userId === this.userIdValue) {
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
        const item  = clone.querySelector<HTMLElement>('[data-role=item]');
        if (item !== null) {
            item.innerHTML = message;
        }
        return clone;
    }
}
