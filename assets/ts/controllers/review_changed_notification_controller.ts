import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLElement> {
    public static values = {userId: Number, reviewId: Number};

    private readonly declare userIdValue: number;
    private readonly declare reviewIdValue: number;

    public connect(): void {
        document.addEventListener(`/review/${String(this.reviewIdValue)}`, this.handleNotification.bind(this));
    }

    public reload(event: Event): void {
        event.preventDefault();
        location.reload();
    }

    private handleNotification(event: Event): void {
        const userId   = this.userIdValue;
        const reviewId = this.reviewIdValue;
        const data     = (event as CustomEvent).detail;

        // notification for different review
        if (data.reviewId !== reviewId) {
            return;
        }

        // notification from me
        if (data.userId === userId) {
            return;
        }

        this.element.style.display = 'block';
    }
}
