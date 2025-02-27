import {Controller} from '@hotwired/stimulus';
import axios from 'axios';
import Assert from '../lib/Assert';
import DataSet from '../lib/DataSet';
import Elements from '../lib/Elements';
import Events from '../lib/Events';
import Function from '../lib/Function';
import ReviewFileTreeService from '../service/ReviewFileTreeService';
import ReviewNotificationService from '../service/ReviewNotificationService';

export default class extends Controller<HTMLElement> {
    public static targets                = ['activeFile'];
    private readonly notificationService = new ReviewNotificationService();
    private readonly fileTreeService     = new ReviewFileTreeService();
    private readonly declare activeFileTarget: HTMLElement;
    private readonly declare hasActiveFileTarget: boolean;
    private reviewId: number             = 0;
    private isNavigating: boolean        = false;

    public connect(): void {
        this.reviewId = DataSet.int(this.element, 'reviewId');

        if (this.hasActiveFileTarget) {
            this.activeFileTarget.scrollIntoView({block: 'center'});
        }
        document.addEventListener('keyup', this.onNavigate.bind(this));
        this.notificationService.subscribe(
            '/review/' + String(this.reviewId),
            ['comment-added', 'comment-removed', 'comment-resolved', 'comment-unresolved'],
            this.updateReviewFileTree.bind(this),
            this.reviewId
        );
    }

    public disconnect(): void {
        this.notificationService.unsubscribe('/review/' + String(this.reviewId));
    }

    public updateReviewFileTree(): void {
        const url      = DataSet.string(this.element, 'url');
        const filePath = DataSet.stringOrNull(this.element, 'selectedFile');
        this.fileTreeService.getReviewFileTree(url, filePath)
            .then(element => this.element.replaceWith(element))
            .catch(Function.empty);
    }

    public toggleFileSeenStatus(event: Event): void {
        const target     = event.target as HTMLElement;
        const parentFile = Assert.notNull(target.closest('[data-role~=review-file-tree-file]'));

        if (target.dataset.seenStatus === '1') {
            target.classList.remove('seen');
            Assert.notNull(parentFile.querySelector<HTMLElement>('[data-role="file-tree-url"]')).dataset.unseen = '1';
            parentFile.classList.add('review-file-tree--unseen');
            target.dataset.seenStatus = '0';
        } else {
            target.classList.add('seen');
            Assert.notNull(parentFile.querySelector<HTMLElement>('[data-role="file-tree-url"]')).dataset.unseen = '0';
            parentFile.classList.remove('review-file-tree--unseen');
            target.dataset.seenStatus = '1';
        }

        axios
            .post(
                `/app/reviews/${target.dataset.reviewId}/file-seen-status`,
                {filePath: target.dataset.filePath, seen: target.dataset.seenStatus},
                {headers: {'Content-Type': 'multipart/form-data'}}
            )
            .catch(Function.empty);
    }

    public onNavigate(event: KeyboardEvent): void {
        if (event.altKey === false || event.shiftKey || this.isNavigating || ['ArrowUp', 'ArrowDown'].includes(event.key) === false) {
            return;
        }

        const selected = this.element.querySelector<HTMLElement>('[data-role="file-tree-url"][data-selected="1"]');
        let files      = Array.from(this.element.querySelectorAll<HTMLElement>('[data-role="file-tree-url"]'));
        if (event.key === 'ArrowUp') {
            files = files.reverse();
        }

        Events.stop(event);
        let i = selected === null ? 0 : (files.indexOf(selected) + 1);

        for (; i < files.length; i++) {
            const file = files[i];
            if (file !== undefined && (event.ctrlKey === false || file.dataset.unseen === '1')) {
                this.isNavigating = true;
                axios.get(`/app/reviews/${this.reviewId}/file-review`, {params: {filePath: file.dataset.reviewFilePath ?? ''}})
                    .then((response) => {
                        document.querySelector('[data-role="file-diff-review"]')?.replaceWith(Elements.create(response.data));
                    })
                    .finally(() => {
                        this.isNavigating = false;
                    });
                break;
            }
        }
    }
}
