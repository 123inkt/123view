import {Injectable} from '@angular/core';

@Injectable({providedIn: 'root'})
export class ReviewActivityFormatter {
    private static readonly TranslationMap = {
        'reviewer-added-by': 'timeline.reviewer.added.by',
        'reviewer-added': 'timeline.reviewer.added',
        'reviewer-removed-by': 'timeline.reviewer.removed.by',
        'reviewer-removed': 'timeline.reviewer.removed',
        'review-created': 'timeline.review.created.from.revision',
        'review-closed': 'timeline.review.closed',
        'reviewer-state-changed-accepted': 'timeline.reviewer.accepted',
        'reviewer-state-changed-rejected': 'timeline.reviewer.rejected',
        'review-accepted': 'timeline.review.accepted',
        'review-rejected': 'timeline.review.rejected',
        'review-opened': 'timeline.review.opened',
        'review-resumed': 'timeline.review.resumed',
        'review-revision-added': 'timeline.review.revision.added',
        'review-revision-removed': 'timeline.review.revision.removed',
        'comment-added': 'timeline.comment.added',
        'comment-reply-added': 'timeline.comment.reply.added',
        'comment-removed': 'timeline.comment.removed',
        'comment-resolved': 'timeline.comment.resolved',
        'comment-unresolved': 'timeline.comment.unresolved'
    };
}
