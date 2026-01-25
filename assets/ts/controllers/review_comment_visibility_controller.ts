import {Controller} from '@hotwired/stimulus';
import DataSet from '../lib/DataSet';
import Strings from '../lib/Strings';
import CommentService from '../service/CommentService';

export default class extends Controller<HTMLElement> {
    public static targets = ['dropdown', 'icon', 'comment'];

    private readonly declare hasDropdownTarget: boolean;
    private readonly declare dropdownTarget: HTMLElement;
    private readonly declare iconTarget: HTMLElement;
    private readonly declare commentTargets: HTMLElement[];

    private readonly commentService = new CommentService();

    public connect(): void {
        if (this.hasDropdownTarget) {
            this.dropdownTarget.addEventListener('change', this.onSelect.bind(this));
        }
    }

    private onSelect(event: Event): void {
        const visibility = (event.target as HTMLInputElement).value;

        // hide dropdown after selection
        this.dropdownTarget.style.display = '';

        // update icon class
        this.iconTarget.className = DataSet.string(this.iconTarget, `iconClass${Strings.capitalize(visibility)}`);

        // update comment visibility
        switch (visibility) {
            case 'none':
                this.commentTargets.forEach(comment => comment.classList.add('d-none'));
                break;
            case 'unresolved':
                this.commentTargets.forEach(
                    comment => comment.classList[DataSet.int(comment, 'commentUnresolved') === 1 ? 'remove' : 'add']('d-none')
                );
                break;
            case 'all':
                this.commentTargets.forEach(comment => comment.classList.remove('d-none'));
                break;
        }

        // remember choice
        void this.commentService.setCommentVisibility(visibility);
    }
}
