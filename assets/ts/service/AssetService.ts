import HttpClient from '../lib/HttpClient';

export default class AssetService {
    private readonly client = new HttpClient();

    public uploadImage(mimeType: string, base64data: string): Promise<string> {
        return this.client.post(
            '/app/assets',
            {mimeType, data: base64data},
            {headers: {'Content-Type': 'multipart/form-data'}}
        ).then(response => (response.data as {url: string}).url);
    }
}
