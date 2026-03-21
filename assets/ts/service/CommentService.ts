import type CommentCount from '../entity/CommentCount';
import Elements from '../lib/Elements';
import HttpClient from '../lib/HttpClient';

export default class CommentService {
    private readonly client = new HttpClient();

    public getMarkdownPreview(comment: string): Promise<string> {
        return this.client
            .get('/app/reviews/comment/markdown', {params: {message: comment}})
            .then((response) => response.data as string);
    }

    public getAddCommentForm(url: string, oldPath: string, newPath: string, line: number, offset: number, lineAfter: number, headSha: string, state: string): Promise<HTMLElement> {
        return this.client
            .get(url, {params: {oldPath, newPath, line, offset, lineAfter, state, headSha}})
            .then(response => (response.data as string))
            .then(html => Elements.create(html));
    }

    public submitAddCommentForm(form: HTMLFormElement): Promise<string> {
        return this.client.form(form).then(response => (response.data as {commentUrl: string}).commentUrl);
    }

    public submitCommentForm(form: HTMLFormElement): Promise<number> {
        return this.client.form(form).then(response => (response.data as {commentId: number}).commentId);
    }

    public getCommentThread(url: string, action?: string): Promise<HTMLElement> {
        let params = {};
        if (action !== undefined) {
            params = {params: {action}};
        }

        return this.client
            .get(url, params)
            .then(response => (response.data as string))
            .then(html => Elements.create(html));
    }

    public deleteComment(url: string): Promise<void> {
        return this.client.delete(url);
    }

    public changeCommentState(url: string, state: string): Promise<void> {
        return this.client.post(url, {state}, {headers: {'Content-Type': 'application/x-www-form-urlencoded'}});
    }

    public addCommentReaction(url: string, message: string): Promise<void> {
        return this.client.post(url, message, {headers: {'Content-Type': 'application/text'}});
    }

    public deleteCommentReply(url: string): Promise<void> {
        return this.client.delete(url);
    }

    public setCommentVisibility(visibility: string): Promise<void> {
        return this.client.post('/app/reviews/comment-visibility', {visibility}, {headers: {'Content-Type': 'application/x-www-form-urlencoded'}});
    }

    public getCommentCount(reviewId: number): Promise<CommentCount> {
        return this.client
            .get(`/app/reviews/${reviewId}/comment-count`, {headers: {Accept: 'application/json'}})
            .then(response => response.data as CommentCount);
    }
}
