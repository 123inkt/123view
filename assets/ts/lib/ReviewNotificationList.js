import Controller from './Controller.js';

export default class ReviewNotificationList extends Controller {
    events = [
        'review-accepted',
        'review-rejected',
        'review-closed',
        'comment-resolved',
        'comment-added',
        'comment-reply-added',
    ]

    connect() {
        document.addEventListener('notification', this.handleNotification.bind(this));
    }

    /**
     * @private
     */
    handleNotification(event) {
        const userId   = parseInt(this.el.dataset.userId);
        const reviewId = parseInt(this.el.dataset.reviewId);
        const data     = event.detail;

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

        this.el.appendChild(this.createItem(data.message));
    }

    /**
     * @private
     */
    createItem(message) {
        const clone = this.role('template').content.cloneNode(true);

        clone.querySelector('[data-role=item]').innerHTML = message;
        return clone;
    }
}
