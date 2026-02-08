import {Controller} from '@hotwired/stimulus';
import {useDebounce} from 'stimulus-use';
import Errors from '../lib/Errors';
import Events from '../lib/Events';
import Mentions from '../lib/Mentions';
import MentionsDropdown from '../lib/MentionsDropdown';
import CommentService from '../service/CommentService';

export default class extends Controller<HTMLElement> {
    public static debounces = ['commentPreviewListener'];
    public static targets   = ['textarea', 'mentionSuggestions', 'markdownPreview', 'form', 'submitButton'];
    public static values    = {actors: String};

    private readonly commentService = new CommentService();
    private readonly declare formTarget: HTMLFormElement;
    private readonly declare textareaTarget: HTMLTextAreaElement;
    private readonly declare mentionSuggestionsTarget: HTMLElement;
    private readonly declare markdownPreviewTarget: HTMLElement;
    private readonly declare submitButtonTarget: HTMLButtonElement;
    private readonly declare actorsValue: string;
    private submitting              = false;

    public connect(): void {
        useDebounce(this, {wait: 150});
        this.textareaTarget.focus();
        const actors = this.actorsValue === '' ? [] : this.actorsValue.split(',');
        new Mentions(this.textareaTarget, actors, new MentionsDropdown(this.mentionSuggestionsTarget)).bind();
        this.textareaTarget.addEventListener('keydown', this.commentCancelListener.bind(this));
        this.textareaTarget.addEventListener('input', this.commentPreviewListener.bind(this));
        this.formTarget.addEventListener('submit', this.submitComment.bind(this));
        this.commentPreviewListener(this.textareaTarget);
    }

    public cancelComment(): void {
        const commentId = this.element.dataset.commentId;
        if (commentId === undefined) {
            this.element.remove();
        } else {
            window.dispatchEvent(new CustomEvent('comment-update', {detail: commentId}));
        }
    }

    public submitComment(event: Event): void {
        Events.stop(event);
        if (this.submitting) {
            return;
        }
        this.submitting     = true;
        const commentThread = this.element.closest<HTMLElement>('[data-controller="comment-thread"]') !== null;

        if (commentThread) {
            this.commentService
                .submitCommentForm(this.formTarget)
                .then(commentId => window.dispatchEvent(new CustomEvent('comment-update', {detail: commentId})))
                .catch(err => {
                    this.submitButtonTarget.disabled  = false;
                    this.submitting = false;
                    Errors.catch(err);
                });
        } else {
            this.commentService
                .submitAddCommentForm(this.formTarget)
                .then(commentUrl => this.commentService.getCommentThread(commentUrl))
                .then(thread => this.element.replaceWith(thread))
                .catch(err => {
                    this.submitButtonTarget.disabled  = false;
                    this.submitting = false;
                    Errors.catch(err);
                });
        }
    }

    private commentCancelListener(event: KeyboardEvent): void {
        if (event.key === 'Escape') {
            event.stopPropagation();
            event.preventDefault();
            this.cancelComment();
        }
    }

    private commentPreviewListener(event: Event | HTMLTextAreaElement): void {
        const target  = event instanceof HTMLTextAreaElement ? event : event.target as HTMLTextAreaElement;
        const comment = target.value.trim();

        if (comment.length === 0) {
            this.markdownPreviewTarget.innerHTML = '';
            return;
        }

        this.commentService
            .getMarkdownPreview(comment)
            .then(html => this.markdownPreviewTarget.innerHTML = html)
            .catch(Errors.catch);
    }
}
