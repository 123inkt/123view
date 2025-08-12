import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLElement> {
    public connect(): void {
        // Check if there's a comment hash in the URL when the page loads
        this.handleCommentDeepLink();

        // Listen for hash changes to handle navigation
        window.addEventListener('hashchange', this.handleCommentDeepLink.bind(this));
    }

    public disconnect(): void {
        window.removeEventListener('hashchange', this.handleCommentDeepLink.bind(this));
    }

    private handleCommentDeepLink(): void {
        const hash = window.location.hash;

        // Check if the hash is a comment link (format: #comment-123)
        if (hash && hash.match(/^#comment-\d+$/)) {
            const commentElement = document.querySelector(hash);

            if (commentElement) {
                // Scroll to the comment with smooth behavior
                commentElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Add a temporary highlight effect
                this.highlightComment(commentElement as HTMLElement);
            }
        }
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
