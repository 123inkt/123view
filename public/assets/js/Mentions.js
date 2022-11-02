export default class Comment {
    textarea;
    dropdown;
    visible = false;

    constructor(textarea, dropdown) {
        this.textarea = textarea;
        this.dropdown = dropdown;
    }

    bind() {
        this.textarea.addEventListener('keydown', this.mentionListener.bind(this));
    }

    /** @private */
    mentionListener(event) {
        if (event.key === '@') {
            this.dropdown.style.display = 'block';
            this.visible                = true;
        }

        if (this.visible === true) {
            this.updateSuggestions(this.getMentionFromTextarea());
        }

        // hide dropdown
        if (this.visible === true && event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            this.dropdown.style.display = 'none';
            this.dropdown.innerHTML     = '';
            this.visible                = false;
        }
    }

    /** @private */
    updateSuggestions(searchQuery) {
        axios.get('/app/user/mentions?search=' + encodeURI(searchQuery))
                .then(response => {
                    this.dropdown.innerHTML = response.data
                            .map(user => `<li><a class="dropdown-item" href="javascript:;" data-user-id="${user.id}">${user.name}</a></li>`)
                            .join('');
                });
    }

    getMentionFromTextarea() {
        const text = this.textarea.value.substring(0, this.textarea.selectionStart);
        return text.substring(text.lastIndexOf('@') + 1);
    }
}
