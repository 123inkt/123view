import {Controller} from '@hotwired/stimulus';
import DataSet from '../lib/DataSet';

export default class extends Controller<HTMLElement> {
    public static targets    = ['dropdown', 'icon', 'comment'];
    private readonly declare dropdownTarget: HTMLElement;
    private readonly declare iconTarget: HTMLElement;
    private readonly declare commentTargets: HTMLElement[];
    private reviewId: number = 0;

    public connect(): void {
        this.dropdownTarget.addEventListener('change', this.onSelect.bind(this));
        this.reviewId = DataSet.int(this.element, 'reviewId');
        this.restore();
    }

    public onSelect(event: Event): void {
        const value = (event.target as HTMLInputElement).value;

        // hide dropdown after selection
        this.dropdownTarget.style.display = '';

        // update visibility
        this.updateCommentVisibility(value);

        // store value in local storage
        localStorage.setItem('review-comment-visibility-' + String(this.reviewId), value);
    }

    private restore(): void {
        const value = localStorage.getItem('review-comment-visibility-' + String(this.reviewId));
        if (value === null) {
            return;
        }

        const element = this.dropdownTarget.querySelector<HTMLInputElement>(`input[value="${value}"]`);
        if (element !== null) {
            element.checked = true;
        }
        this.updateCommentVisibility(value);
    }

    private updateCommentVisibility(visibility: string): void {
        switch (visibility) {
            case 'none':
                this.iconTarget.className = 'bi bi-chat';
                this.commentTargets.forEach(comment => comment.style.display = 'none');
                break;
            case 'unresolved':
                this.iconTarget.className = 'bi bi-chat-dots';
                this.commentTargets.forEach(comment => comment.style.display = DataSet.int(comment, 'commentUnresolved') === 1 ? '' : 'none');
                break;
            case 'all':
                this.iconTarget.className = 'bi bi-chat-fill';
                this.commentTargets.forEach(comment => comment.style.display = '');
                break;
        }
    }
}
