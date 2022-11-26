export default class Comment {
    textarea;
    dropdown;
    visible = false;

    /**
     * @param {HTMLElement} textarea
     * @param {MentionsDropdown} dropdown
     */
    constructor(textarea, dropdown) {
        this.textarea = textarea;
        this.dropdown = dropdown;
    }

    bind() {
        this.textarea.addEventListener('keydown', this.onKeyDown.bind(this));
        this.textarea.addEventListener('input', this.onInput.bind(this));
        this.dropdown.addEventListener('click', this.onClick.bind(this));
    }

    /** @private */
    onKeyDown(event) {
        // show dropdown
        if (event.key === '@') {
            this.dropdown.show();
        }

        // hide dropdown
        if (this.dropdown.isVisible() && event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
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

    /** @private */
    onInput() {
        if (this.dropdown.isVisible()) {
            const mention = this.getMentionFromTextarea();
            if (mention === null) {
                this.dropdown.hide();
                return;
            }
            this.getSuggestions(this.getMentionFromTextarea(), (users) => this.dropdown.setUsers(users));
        }
    }

    /** @private */
    onClick(event) {
        this.dropdown.hide();
        this.textarea.focus();
        this.updateMentionInTextarea(this.dropdown.getSelectedUser(event.target));
    }

    /** @private */
    getSuggestions(searchQuery, callback) {
        axios.get('/app/user/mentions?search=' + encodeURI(searchQuery)).then(response => callback(response.data));
    }

    /** @private */
    getMentionFromTextarea() {
        const text           = this.textarea.value.substring(0, this.textarea.selectionStart);
        const indexOfMention = text.lastIndexOf('@');
        return indexOfMention === -1 ? null : text.substring(indexOfMention + 1);
    }

    /** @private */
    updateMentionInTextarea(user) {
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

        // set cursor position
        this.textarea.selectionEnd = textBeforeMention.length + replacement.length;
    }
}
