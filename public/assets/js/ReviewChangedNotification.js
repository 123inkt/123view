import Controller from './Controller.js';

export default class ReviewChangedNotification extends Controller {
    connect() {
        document.addEventListener('notification', this.handleNotification.bind(this));
        this.listen('click', 'reload', (_target, evt) => {
            evt.preventDefault();
            location.reload()
        });
    }

    handleNotification(event) {
        const userId = parseInt(this.el.dataset.userId);
        const reviewId = parseInt(this.el.dataset.reviewId);
        const data = event.detail;

        // notification for different review
        if (data.reviewId !== reviewId) {
            return;
        }

        // notification from me
        if (data.userId === userId) {
            return;
        }

        this.el.style.display = 'block';
    }
}
