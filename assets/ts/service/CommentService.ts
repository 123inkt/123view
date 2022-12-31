import Elements from '../lib/Elements';
import HttpClient from '../lib/HttpClient';

export default class CommentService {
    private readonly client = new HttpClient();

    public getAddCommentForm(url: string, filePath: string, line: number, offset: number, lineAfter: number): Promise<HTMLElement> {
        return this.client
            .get(url, {params: {filePath, line, offset, lineAfter}})
            .then(response => response.data)
            .then(html => Elements.create(html));
    }
}
