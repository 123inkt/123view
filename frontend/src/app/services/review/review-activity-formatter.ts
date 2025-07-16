import {Injectable} from '@angular/core';
import {basename} from '@lib/Path';
import CodeReviewActivity from '@model/entities/CodeReviewActivity';
import {TranslateService} from '@ngx-translate/core';
import {TokenStore} from '@service/auth/token-store';
import {Observable, of, switchMap} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ReviewActivityFormatter {
    private static readonly TranslationMap: {[key: string]: string} = {
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

    constructor(private readonly tokenStore: TokenStore, private readonly translator: TranslateService) {
    }

    public formatActivity(activity: CodeReviewActivity): Observable<string> {
        const translationKey: string = ReviewActivityFormatter.TranslationMap[activity.eventName];
        const filepath: string       = String(activity.data['file'] ?? 'unknown file');
        const filename: string       = basename(filepath);

        return this.formatUsername(activity)
            .pipe(switchMap((username) => this.translator.get(translationKey, {username: username, file: filename})));
    }

    private formatUsername(activity: CodeReviewActivity): Observable<string> {
        // if user is unknown, activity was executed by the system
        if (activity.user === undefined) {
            return of('123view');
        }
        // user is current user, show as 'You'
        if (this.tokenStore.getUserIdentifier() === activity.user.email) {
            return this.translator.get('you');
        }
        return of(activity.user.name);
    }
}
