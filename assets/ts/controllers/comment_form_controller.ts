import {Controller} from '@hotwired/stimulus';
import {useThrottle} from 'stimulus-use';
import DataSet from '../lib/DataSet';
import Errors from '../lib/Errors';
import Events from '../lib/Events';
import Mentions from '../lib/Mentions';
import MentionsDropdown from '../lib/MentionsDropdown';
import CommentService from '../service/CommentService';

export default class extends Controller<HTMLElement> {
    public static throttles = ['commentPreviewListener'];
    public static targets   = ['textarea', 'mentionSuggestions', 'markdownPreview', 'form']

    private readonly commentService = new CommentService();
    private declare formTarget: HTMLFormElement;
    private declare textareaTarget: HTMLTextAreaElement;
    private declare mentionSuggestionsTarget: HTMLElement;
    private declare markdownPreviewTarget: HTMLElement;

    public connect(): void {
        useThrottle(this, {wait: 150});
        this.textareaTarget.focus();
        new Mentions(this.textareaTarget, new MentionsDropdown(this.mentionSuggestionsTarget)).bind();
        this.textareaTarget.addEventListener('input', this.commentPreviewListener.bind(this));
        this.formTarget.addEventListener('submit', this.submitComment.bind(this));
        this.commentPreviewListener(this.textareaTarget);
    }

    public cancelComment(): void {
        const commentId = DataSet.int(this.element, 'commentId');
        window.dispatchEvent(new CustomEvent('comment-update', {detail: commentId}))
    }

    public submitComment(event: Event): void {
        Events.stop(event);

        const commentThread = this.element.closest<HTMLElement>('[data-controller="comment-thread"]') !== null;

        if (commentThread) {
            this.commentService
                .submitCommentForm(this.formTarget)
                .then(commentId => window.dispatchEvent(new CustomEvent('comment-update', {detail: commentId})))
                .catch(Errors.catch);
        } else {
            this.commentService
                .submitAddCommentForm(this.formTarget)
                .then(commentUrl => this.commentService.getCommentThread(commentUrl))
                .then(commentThread => this.element.replaceWith(commentThread))
                .catch(Errors.catch);
        }
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
            .catch(Errors.catch);
    }
}
