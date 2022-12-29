import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public static values = {userId: Number, reviewId: Number};

    declare userIdValue: number;
    declare reviewIdValue: number;

    public connect(): void {
        document.addEventListener('notification', this.handleNotification.bind(this));
    }

    public reload(event: Event): void {
        event.preventDefault();
        location.reload()
    }

    private handleNotification(event: Event): void {
        const userId   = this.userIdValue;
        const reviewId = this.reviewIdValue;
        const data     = (<CustomEvent>event).detail;

        // notification for different review
        if (data.reviewId !== reviewId) {
            return;
        }

        // notification from me
        if (data.userId === userId) {
            return;
        }

        (<HTMLElement>this.element).style.display = 'block';
    }
}
