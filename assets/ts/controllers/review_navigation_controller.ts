import {Controller} from '@hotwired/stimulus';
import axios from 'axios';
import DataSet from '../lib/DataSet';
import Elements from '../lib/Elements';
import Events from '../lib/Events';
import type ReviewFileTreeController from './review_file_tree_controller';

export default class extends Controller<HTMLElement> {
    public static values                     = {reviewId: Number};
    public static targets                    = ['reviewFileTree'];
    private isNavigating                     = false;
    private reviewId                         = 0;
    declare private readonly reviewFileTreeTarget: HTMLElement;
    private navigationAbort: AbortController = new AbortController;

    public connect(): void {
        this.reviewId = DataSet.int(this.element, 'reviewId');
        document.addEventListener('keyup', this.onNavigate.bind(this));
        window.addEventListener('popstate', this.onBackTrack.bind(this));
    }

    public onNavigate(event: KeyboardEvent): void {
        const keys = ['ArrowUp', 'ArrowLeft', 'ArrowDown', 'ArrowRight'];
        if (event.altKey === false || event.shiftKey || this.isNavigating || keys.includes(event.key) === false) {
            return;
        }

        const next   = event.key !== 'ArrowUp' && event.key !== 'ArrowLeft' ? 'down' : 'up';
        const unseen = event.ctrlKey;
        Events.stop(event);

        const controller = this.getFileTreeController();
        const file       = controller.findNextFile(next, unseen);
        if (file === null) {
            return;
        }

        if (this.isNavigating) {
            return;
        }
        this.isNavigating = true;

        this.navigationAbort.abort();
        this.navigationAbort = new AbortController();
        axios.get(`/app/reviews/${this.reviewId}/file-review`, {
            params: {filePath: file.dataset.reviewFilePath ?? ''},
            signal: this.navigationAbort.signal
        })
            .then((response) => {
                controller.selectFile(file);
                history.pushState({reviewId: this.reviewId, filePath: file.dataset.reviewFilePath}, '', String(file.getAttribute('href')))
                document.querySelector('[data-role~="file-diff-review"]')?.replaceWith(Elements.create(response.data as string));
            })
            .finally(() => {
                this.isNavigating = false;
            });
    }

    public onBackTrack(event: PopStateEvent): void {
        const state = event.state as {reviewId: number, filePath: string} | null;
        if (state === null) {
            return;
        }

        const controller = this.getFileTreeController();
        const file       = controller.findFile(state.filePath);
        if (file === null) {
            console.info('Unable to find file for filepath', state);
            return;
        }

        this.navigationAbort.abort();
        this.navigationAbort = new AbortController();
        axios.get(`/app/reviews/${this.reviewId}/file-review`, {
            params: {filePath: file.dataset.reviewFilePath ?? ''},
            signal: this.navigationAbort.signal
        })
            .then((response) => {
                controller.selectFile(file);
                document.querySelector('[data-role="file-diff-review"]')?.replaceWith(Elements.create(response.data as string));
            });
    }

    private getFileTreeController(): ReviewFileTreeController {
        return this.application.getControllerForElementAndIdentifier(
            this.reviewFileTreeTarget,
            'review-file-tree'
        ) as ReviewFileTreeController;
    }
}
