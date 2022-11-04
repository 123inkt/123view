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
        const parentFile = target.closest('[data-role=review-file-tree-file]');

        if (target.dataset.seenStatus === '1') {
            target.classList.remove('seen');
            parentFile.classList.add('review-file-tree--unseen')
            target.dataset.seenStatus = '0';
        } else {
            target.classList.add('seen');
            parentFile.classList.remove('review-file-tree--unseen')
            target.dataset.seenStatus = '1';
        }

        axios.post(
                `/app/reviews/${target.dataset.reviewId}/file-seen-status`,
                {filePath: target.dataset.filePath, seen: target.dataset.seenStatus},
                {headers: {'Content-Type': 'multipart/form-data'}}
        );
    }
}
