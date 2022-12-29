import {Controller} from '@hotwired/stimulus';
import axios from 'axios';
import Assert from '../../lib/Assert';

export default class extends Controller {
    public static targets = ['activeFile']
    declare activeFileTarget: HTMLElement;
    declare hasActiveFileTarget: boolean;

    public connect(): void {
        if (this.hasActiveFileTarget) {
            this.activeFileTarget.scrollIntoView({block: 'center'});
        }
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
