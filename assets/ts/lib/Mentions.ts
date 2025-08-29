import axios from 'axios';
import Function from './Function';
import type MentionsDropdown from './MentionsDropdown';
import type User from './User';

export default class Mentions {
    public visible = false;

    constructor(
        private readonly textarea: HTMLTextAreaElement,
        private readonly preferredUserIds: string[],
        private readonly dropdown: MentionsDropdown) {
    }

    public bind(): void {
        this.textarea.addEventListener('keydown', this.onKeyDown.bind(this));
        this.textarea.addEventListener('input', this.onInput.bind(this));
        this.dropdown.addEventListener('click', this.onClick.bind(this));
    }

    private onKeyDown(event: KeyboardEvent): void {
        // show dropdown
        if (event.key === '@') {
            this.dropdown.show();
        }

        // hide dropdown
        if (this.dropdown.isVisible() && event.key === 'Escape') {
            event.preventDefault();
            event.stopImmediatePropagation();
            this.dropdown.hide();
        }

        // select next suggestion
        if (this.dropdown.isVisible() && event.key === 'ArrowDown') {
            event.preventDefault();
            event.stopPropagation();
            this.dropdown.selectNext();
        }

        // select previous suggestion
        if (this.dropdown.isVisible() && event.key === 'ArrowUp') {
            event.preventDefault();
            event.stopPropagation();
            this.dropdown.selectPrev();
        }

        // select current user
        if (this.dropdown.isVisible() && event.key === 'Enter') {
            event.preventDefault();
            event.stopPropagation();
            this.dropdown.hide();
            this.updateMentionInTextarea(this.dropdown.getSelectedUser());
        }
    }

    private onInput(): void {
        if (this.dropdown.isVisible()) {
            const mention = this.getMentionFromTextarea();
            if (mention === null || mention.includes(' ')) {
                this.dropdown.hide();
                return;
            }
            this.getSuggestions(this.getMentionFromTextarea(), (users) => this.dropdown.setUsers(users));
        }
    }

    private onClick(event: Event): void {
        this.dropdown.hide();
        this.textarea.focus();
        this.updateMentionInTextarea(this.dropdown.getSelectedUser(event.target as HTMLElement));
    }

    private getSuggestions(searchQuery: string | null, callback: (data: User[]) => void): void {
        const params = new URLSearchParams({search: searchQuery ?? '', preferredUserIds: this.preferredUserIds.join(',')});
        axios
            .get('/app/user/mentions?' + params.toString())
            .then(response => callback(response.data as User[]))
            .catch(Function.empty);
    }

    private getMentionFromTextarea(): string | null {
        const text           = this.textarea.value.substring(0, this.textarea.selectionStart);
        const indexOfMention = text.lastIndexOf('@');
        return indexOfMention === -1 ? null : text.substring(indexOfMention + 1);
    }

    /** @private */
    private updateMentionInTextarea(user: User | undefined): void {
        if (user === undefined) {
            return;
        }
        const replacement       = `@user:${user.id}[${user.name}]`;
        const text              = this.textarea.value;
        const textBeforeCaret   = text.substring(0, this.textarea.selectionStart);
        const textAfterCaret    = text.substring(this.textarea.selectionStart);
        const textBeforeMention = textBeforeCaret.substring(0, text.lastIndexOf('@'));

        // update textarea content
        this.textarea.value = textBeforeMention + replacement + ' ' + textAfterCaret;
        this.textarea.dispatchEvent(new Event('input', {bubbles: true, cancelable: true}));

        // set cursor position
        this.textarea.selectionEnd = textBeforeMention.length + replacement.length;
    }
}
