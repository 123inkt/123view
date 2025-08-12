import {Controller} from '@hotwired/stimulus';

export default class extends Controller<HTMLElement> {
    static targets = ['link'];

    declare readonly linkTarget: HTMLAnchorElement;

    public copyLink(event: Event): void {
        event.preventDefault();

        const link      = event.currentTarget as HTMLAnchorElement;
        const commentId = link.dataset.commentId;

        if (!commentId) {
            console.error('Comment ID not found');
            return;
        }

        // Build URL based on current page context
        const url           = this.buildCommentUrl(commentId);
        const originalTitle = link.title;

        // Try modern clipboard API first
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                this.showCopyFeedback(link, originalTitle);
            }).catch((err) => {
                console.warn('Modern clipboard failed, trying fallback:', err);
                this.fallbackCopyToClipboard(url, link, originalTitle);
            });
        } else {
            // Fallback for older browsers
            this.fallbackCopyToClipboard(url, link, originalTitle);
        }
    }

    private buildCommentUrl(commentId: string): string {
        const currentUrl = new URL(window.location.href);

        // Check if we're on a file page (has filePath parameter)
        const filePath = currentUrl.searchParams.get('filePath');

        if (filePath) {
            // We're on a file page, preserve the filePath parameter
            const filePageUrl = new URL(window.location.href);
            filePageUrl.hash  = `comment-${commentId}`;
            return filePageUrl.toString();
        } else {
            // We're on the review page, use the base review URL
            const reviewUrl = window.location.origin + window.location.pathname;
            return `${reviewUrl}#comment-${commentId}`;
        }
    }

    private fallbackCopyToClipboard(text: string, link: HTMLAnchorElement, originalTitle: string): void {
        const textArea     = document.createElement('textarea');
        textArea.value     = text;
        textArea.className = 'comment-clipboard-fallback-textarea';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                this.showCopyFeedback(link, originalTitle);
            } else {
                this.showErrorFeedback(link, originalTitle);
            }
        } catch (err) {
            console.error('Failed to copy: ', err);
            this.showErrorFeedback(link, originalTitle);
        }

        document.body.removeChild(textArea);
    }

    private showCopyFeedback(link: HTMLAnchorElement, originalTitle: string): void {
        // Visual feedback on the link itself
        link.classList.add('comment__deeplink--success');
        link.title = 'Link copied to clipboard!';

        // Create a prominent notification
        this.showNotification('✓ Comment link copied to clipboard!', 'success');

        // Reset after 2 seconds
        setTimeout(() => {
            link.classList.remove('comment__deeplink--success');
            link.title = originalTitle;
        }, 2000);
    }

    private showErrorFeedback(link: HTMLAnchorElement, originalTitle: string): void {
        link.classList.add('comment__deeplink--error');
        link.title = 'Failed to copy link';

        // Create an error notification
        this.showNotification('✗ Failed to copy link', 'error');

        setTimeout(() => {
            link.classList.remove('comment__deeplink--error');
            link.title = originalTitle;
        }, 2000);
    }

    private showNotification(message: string, type: 'success' | 'error'): void {
        // Create notification element
        const notification       = document.createElement('div');
        notification.className   = `comment-clipboard-notification comment-clipboard-notification--${type}`;
        notification.textContent = message;

        // Add to page
        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.add('comment-clipboard-notification--show');
        }, 10);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('comment-clipboard-notification--show');
            notification.classList.add('comment-clipboard-notification--hide');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}
