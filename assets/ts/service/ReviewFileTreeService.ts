import Elements from '../lib/Elements';
import HttpClient from '../lib/HttpClient';

export default class ReviewFileTreeService {
    private readonly client = new HttpClient();

    public getReviewFileTree(url: string, revisions: string, selectedFile: string|null): Promise<HTMLElement> {
        return this.client
            .get(url, {params: {revisions, selectedFile}})
            .then(response => response.data)
            .then(html => Elements.create(html));
    }
}
