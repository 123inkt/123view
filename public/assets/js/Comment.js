import Controller from './Controller.js';
import Mentions from './Mentions.js';

export default class Comment extends Controller {

    connect() {
        const textarea = this.role('comment-textarea');
        textarea.scrollIntoView({block: 'center'});
        textarea.focus();
        new Mentions(textarea, this.role('mention-suggestions')).bind();
        this.commentResizeListener(textarea);
        this.listen('input', 'comment-textarea', this.commentResizeListener);
        this.listen('keyup', 'comment-textarea', this.commentKeyListener.bind(this));
        this.listen('paste', 'comment-textarea', this.commentPasteListener.bind(this));
        this.listen('click', 'cancel-comment', this.cancelComment.bind(this));
    }

    commentResizeListener(target) {
        target.style.height = "5px";
        target.style.height = Math.max(84, (target.scrollHeight))+"px";
    }

    commentKeyListener(target, event) {
        // ctrl + enter should submit the form
        if (event.key === 'Enter' && event.ctrlKey) {
            target.closest('form').submit();
        }
    }

    commentPasteListener(target, event) {
        if (!event.clipboardData || !event.clipboardData.items || event.clipboardData.items.length !== 1) {
            return;
        }

        const item         = event.clipboardData.items[0];
        const allowedMimes = ['image/png', 'image/gif', 'image/jpg', 'image/jpeg']
        if (item.kind !== 'file' || allowedMimes.includes(item.type) === false) {
            return;
        }

        const blob = item.getAsFile();
        if (blob.size > 2097152) {
            alert('Pasted file size exceeds allowed file size of 2MB');
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const reader  = new FileReader();
        reader.onload = event => {
            // get data base64 encoded string, and grab just the data string
            const base64data = event.target.result.replace(/^[^,]+,/, '')

            axios.post(
                '/app/assets',
                {mimeType: item.type, data: base64data},
                {headers: {'Content-Type': 'multipart/form-data'}}
            ).then(response => {
                // add url to textarea
                const url = response.data.url;

                // insert at cursor
                target.value = target.value.substring(0, target.selectionStart)
                    + "![file](" + url + ")\n"
                    + target.value.substring(target.selectionEnd, target.value.length);
            })
        };
        reader.readAsDataURL(blob);
    }

    cancelComment() {
        const location = new URL(window.location.href);
        location.searchParams.delete('action');
        window.location = location.toString();
    }
}
