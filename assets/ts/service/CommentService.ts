import axios from 'axios';
import Elements from '../lib/Elements';

export default class CommentService {
    public getAddCommentForm(url: string, filePath: string, line: number, offset: number, lineAfter: number): Promise<HTMLElement> {
        return axios.get(url, {params: {filePath, line, offset, lineAfter}})
            .then(response => response.data)
            .then(html => Elements.create(html));
    }
}
