import {Controller} from '@hotwired/stimulus';
import {useDebounce} from 'stimulus-use';
import Assert from '../lib/Assert';
import Function from '../lib/Function';
import InputElement from '../lib/InputElement';
import Mentions from '../lib/Mentions';
import MentionsDropdown from '../lib/MentionsDropdown';
import AssetService from '../service/AssetService';
import CommentService from '../service/CommentService';

export default class Comment extends Controller {
    public static debounces = ['commentPreviewListener'];
    public static targets   = ['textarea', 'mentionSuggestions', 'markdownPreview']

    private readonly assetService   = new AssetService();
    private readonly commentService = new CommentService();
    private declare textareaTarget: HTMLTextAreaElement;
    private declare mentionSuggestionsTarget: HTMLElement;
    private declare markdownPreviewTarget: HTMLElement;

    public connect(): void {
        useDebounce(this, {wait: 100});
        const textarea = this.textareaTarget;
        textarea.focus();
        new Mentions(textarea, new MentionsDropdown(this.mentionSuggestionsTarget)).bind();
        this.commentResizeListener(textarea);
        textarea.addEventListener('input', this.commentResizeListener.bind(this));
        textarea.addEventListener('input', this.commentPreviewListener.bind(this));
        textarea.addEventListener('keyup', this.commentKeyListener.bind(this));
        textarea.addEventListener('paste', this.commentPasteListener.bind(this));
    }

    public cancelComment(): void {
        this.element.remove();
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

            this.assetService.uploadImage(mimeType, base64data)
                .then(url => {
                    InputElement.insertAtCursor(target, `![file](${url})\n`);
                    this.commentPreviewListener(target);
                })
                .catch(Function.empty);
        };
        reader.readAsDataURL(blob);
    }

    private commentPreviewListener(event: Event | HTMLTextAreaElement) {
        const target    = event instanceof HTMLTextAreaElement ? event : <HTMLTextAreaElement>event.target;
        const previewEl = this.markdownPreviewTarget!;
        const comment   = target.value.trim();

        if (comment.length === 0) {
            previewEl.innerHTML = '';
            return;
        }

        this.commentService
            .getMarkdownPreview(comment)
            .then(html => previewEl.innerHTML = html)
            .catch(Function.empty);
    }
}
