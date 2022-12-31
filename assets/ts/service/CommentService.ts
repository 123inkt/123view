import Elements from '../lib/Elements';
import HttpClient from '../lib/HttpClient';

export default class CommentService {
    private readonly client = new HttpClient();



    public getMarkdownPreview(comment: string): Promise<string> {
        return this.client
            .get('/app/reviews/comment/markdown', {params: {message: comment}})
            .then((response) => response.data);
    }

    public getAddCommentForm(url: string, filePath: string, line: number, offset: number, lineAfter: number): Promise<HTMLElement> {
        return this.client
            .get(url, {params: {filePath, line, offset, lineAfter}})
            .then(response => response.data)
            .then(html => Elements.create(html));
    }

    public submitAddCommentForm(form: HTMLFormElement): Promise<string> {
        return this.client.form(form).then(response => response.data.commentUrl);
    }

    public getCommentThread(url: string, action?: string): Promise<HTMLElement> {
        let params = {};
        if (action !== undefined) {
            params = {params: {action}};
        }

        return this.client
            .get(url, params)
            .then(response => response.data)
            .then(html => Elements.create(html));
    }

    public deleteComment(url: string): Promise<void> {
        return this.client.delete(url);
    }
}
