import {Controller} from '@hotwired/stimulus';
import axios from 'axios';
import Assert from '../lib/Assert';
import Mentions from '../lib/Mentions';
import MentionsDropdown from '../lib/MentionsDropdown';

export default class Comment extends Controller {
    public static targets = ['textarea', 'mentionSuggestions', 'markdownPreview']

    declare textareaTarget: HTMLTextAreaElement;
    declare mentionSuggestionsTarget: HTMLElement;
    declare markdownPreviewTarget: HTMLElement;
    private abort: AbortController | null = null;

    public connect(): void {
        const textarea = this.textareaTarget;
        textarea.scrollIntoView({block: 'center'});
        textarea.focus();
        new Mentions(textarea, new MentionsDropdown(this.mentionSuggestionsTarget)).bind();
        this.commentResizeListener(textarea);
        textarea.addEventListener('input', this.commentResizeListener.bind(this));
        textarea.addEventListener('input', this.commentPreviewListener.bind(this));
        textarea.addEventListener('keyup', this.commentKeyListener.bind(this));
        textarea.addEventListener('paste', this.commentPasteListener.bind(this));
    }

    public cancelComment(): void {
        const location = new URL(window.location.href);
        location.searchParams.delete('action');
        (window as Window).location = location.toString();
    }

    private commentResizeListener(event: Event | HTMLTextAreaElement): void {
        const target        = event instanceof HTMLTextAreaElement ? event : <HTMLElement>event.target;
        target.style.height = '5px';
        target.style.height = Math.max(84, (target.scrollHeight)) + 'px';
    }

    private commentKeyListener(event: KeyboardEvent): void {
        const target = <HTMLElement>event.target;
        // ctrl + enter should submit the form
        if (event.key === 'Enter' && event.ctrlKey) {
            target.closest('form')?.submit();
        }
    }

    private commentPasteListener(event: ClipboardEvent) {
        const target = <HTMLTextAreaElement>event.target;
        if (!event.clipboardData || !event.clipboardData.items || event.clipboardData.items.length !== 1) {
            return;
        }

        const item         = <DataTransferItem>event.clipboardData.items[0];
        const allowedMimes = ['image/png', 'image/gif', 'image/jpg', 'image/jpeg']
        if (item.kind !== 'file' || allowedMimes.includes(item.type) === false) {
            return;
        }

        const mimeType = item.type;
        const blob     = Assert.notNull(item.getAsFile());
        if (blob.size > 2097152) {
            alert('Pasted file size exceeds allowed file size of 2MB');
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const reader  = new FileReader();
        reader.onload = event => {
            // get data base64 encoded string, and grab just the data string
            const base64data = (<string>event.target!.result).replace(/^[^,]+,/, '')

            axios.post(
                '/app/assets',
                {mimeType: mimeType, data: base64data},
                {headers: {'Content-Type': 'multipart/form-data'}}
            ).then(response => {
                // add url to textarea
                const url = response.data.url;

                // insert at cursor
                target.value = target.value.substring(0, target.selectionStart)
                    + '![file](' + url + ')\n'
                    + target.value.substring(target.selectionEnd, target.value.length);
                this.commentPreviewListener(target);
            })
        };
        reader.readAsDataURL(blob);
    }

    private commentPreviewListener(event: Event|HTMLTextAreaElement) {
        const target    = event instanceof HTMLTextAreaElement ? event : <HTMLTextAreaElement>event.target;
        const previewEl = this.markdownPreviewTarget!;
        const comment   = target.value.trim();

        if (comment.length === 0) {
            previewEl.innerHTML = '';
            return;
        }

        // abort any running requests
        if (this.abort !== null) {
            this.abort.abort();
        }

        this.abort = new AbortController();
        axios.get(
            '/app/reviews/comment/markdown?message=' + encodeURIComponent(comment),
            {signal: this.abort.signal}
        )
            .then((response) => previewEl.innerHTML = response.data)
            .catch(() => {
            })
            .finally(() => this.abort = null);
    }
}
