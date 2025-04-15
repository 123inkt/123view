import {Controller} from '@hotwired/stimulus';
import axios from 'axios';
import DataSet from '../lib/DataSet';
import Elements from '../lib/Elements';
import Events from '../lib/Events';
import ReviewFileTreeController from './review_file_tree_controller';

export default class extends Controller<HTMLElement> {
    public static values          = {reviewId: Number};
    public static targets         = ['reviewFileTree'];
    private isNavigating: boolean = false;
    private reviewId: number      = 0;
    declare private readonly reviewFileTreeTarget: HTMLElement;

    public connect(): void {
        this.reviewId = DataSet.int(this.element, 'reviewId');
        document.addEventListener('keyup', this.onNavigate.bind(this));
    }

    public onNavigate(event: KeyboardEvent): void {
        const keys = ['ArrowUp', 'ArrowLeft', 'ArrowDown', 'ArrowRight'];
        if (event.altKey === false || event.shiftKey || this.isNavigating || keys.includes(event.key) === false) {
            return;
        }

        const next   = event.key !== 'ArrowUp' && event.key !== 'ArrowLeft' ? 'down' : 'up';
        const unseen = event.ctrlKey;
        Events.stop(event);

        const controller = <ReviewFileTreeController>this.application.getControllerForElementAndIdentifier(
            this.reviewFileTreeTarget,
            'review-file-tree'
        );

        const file = controller.findNextFile(next, unseen);
        if (file === null) {
            return;
        }

        if (this.isNavigating) {
            return;
        }
        this.isNavigating = true;

        axios.get(`/app/reviews/${this.reviewId}/file-review`, {params: {filePath: file.dataset.reviewFilePath ?? ''}})
            .then((response) => {
                controller.selectFile(file);
                history.pushState({reviewId: this.reviewId, filePath: file.dataset.reviewFilePath}, '', String(file.getAttribute('href')))
                document.querySelector('[data-role~="file-diff-review"]')?.replaceWith(Elements.create(response.data));
            })
            .finally(() => {
                this.isNavigating = false;
            });
    }
}
