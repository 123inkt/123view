import Controller from './Controller.js';

export default class Review extends Controller {
    connect() {
        try {
            this.role('active-file').scrollIntoView({block: 'center'});
        } catch (e) {
            // active file might not be present.
        }

        this.listen('click', 'file-seen-status', this.toggleFileSeenStatus.bind(this));
    }

    toggleFileSeenStatus(target) {
        const seenStatus = target.dataset.seenStatus;
        const reviewId   = target.dataset.reviewId;

        if (seenStatus === '1') {
            target.classList.remove('seen');
            target.dataset.seenStatus = '0';
        } else {
            target.classList.add('seen');
            target.dataset.seenStatus = '1';
        }
    }
}
