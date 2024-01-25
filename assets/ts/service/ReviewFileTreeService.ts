import Elements from '../lib/Elements';
import HttpClient from '../lib/HttpClient';

export default class ReviewFileTreeService {
    private readonly client = new HttpClient();

    public getReviewFileTree(url: string, filePath: string | null): Promise<HTMLElement> {
        return this.client
            .get(url, {params: {filePath}})
            .then(response => (response.data as string))
            .then(html => Elements.create(html));
    }
}
