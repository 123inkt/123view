import {Controller} from '@hotwired/stimulus';
import axios from 'axios';
import Assert from '../lib/Assert';
import DataSet from '../lib/DataSet';
import Function from '../lib/Function';
import ReviewFileTreeService from '../service/ReviewFileTreeService';
import ReviewNotificationService from '../service/ReviewNotificationService';

export default class extends Controller<HTMLElement> {
    public static targets                = ['activeFile'];
    private readonly notificationService = new ReviewNotificationService();
    private readonly fileTreeService     = new ReviewFileTreeService();
    private declare activeFileTarget: HTMLElement;
    private declare hasActiveFileTarget: boolean;

    public connect(): void {
        if (this.hasActiveFileTarget) {
            this.activeFileTarget.scrollIntoView({block: 'center'});
        }
        document.addEventListener('notification', this.notificationService.onEvent);
        this.notificationService.subscribe(
            ['comment-added', 'comment-removed', 'comment-resolved'],
            this.updateReviewFileTree.bind(this),
            DataSet.int(this.element, 'reviewId')
        );
    }

    public disconnect(): void {
        document.removeEventListener('notification', this.notificationService.onEvent);
    }

    public updateReviewFileTree(): void {
        const url          = DataSet.string(this.element, 'url');
        const revisions    = DataSet.string(this.element, 'revisions');
        const selectedFile = DataSet.stringOrNull(this.element, 'selectedFile');
        this.fileTreeService.getReviewFileTree(url, revisions, selectedFile)
            .then(element => this.element.replaceWith(element))
            .catch(Function.empty);
    }

    public toggleFileSeenStatus(event: Event): void {
        const target     = (<HTMLElement>event.target);
        const parentFile = Assert.notNull(target.closest('[data-role~=review-file-tree-file]'));

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
