import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public connect(): void {
        const hash = window.location.hash;
        if (hash !== '') {
            this.handleHash(hash);
        }

        // Listen for hash changes to handle navigation
        window.addEventListener('hashchange', this.handleHashChange.bind(this));
    }

    public disconnect(): void {
        window.removeEventListener('hashchange', this.handleHashChange.bind(this));
    }

    private handleHashChange(): void {
        const hash = window.location.hash;
        if (hash !== '') {
            this.handleHash(hash);
        }
    }

    private handleHash(hash: string): void {
        const target = this.findTarget(hash);
        if (target !== null) {
            target.scrollIntoView({behavior: 'smooth', block: 'center'});

            // Add highlight effect for comments
            if (this.isCommentHash(hash)) {
                this.highlightComment(target as HTMLElement);
            }
        }
    }

    private findTarget(hash: string): Element | null {
        // Support new email-friendly format: #comment-123
        let matches = hash.match(/^#comment-(\d+)$/);
        if (matches !== null) {
            const commentElement = document.querySelector(hash);
            if (commentElement) {
                return commentElement;
            }
            // Fallback to data-comment-id attribute
            return this.element.querySelector(`[data-comment-id="${matches[1]}"]`);
        }

        // Support new format for replies: #reply-123
        matches = hash.match(/^#reply-(\d+)$/);
        if (matches !== null) {
            const replyElement = document.querySelector(hash);
            if (replyElement) {
                return replyElement;
            }
            // Fallback to data-reply-id attribute
            return this.element.querySelector(`[data-reply-id="${matches[1]}"]`);
        }

        // Support line linking: #focus:line:123
        matches = hash.match(/^#focus:line:(\d+)$/);
        if (matches !== null) {
            return this.element.querySelector(`[data-line="${matches[1]}"]`);
        }

        return null;
    }

    private isCommentHash(hash: string): boolean {
        return hash.match(/^#(comment|reply)-(\d+)$/) !== null;
    }

    private highlightComment(element: HTMLElement): void {
        // Add a highlight class temporarily
        element.classList.add('comment-highlighted');

        // Remove the highlight after 3 seconds
        setTimeout(() => {
            element.classList.remove('comment-highlighted');
        }, 3000);
    }
}
