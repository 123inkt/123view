import {Controller} from '@hotwired/stimulus';
import Assert from '../lib/Assert';
import Function from '../lib/Function';
import InputElement from '../lib/InputElement';
import AssetService from '../service/AssetService';

export default class extends Controller<HTMLTextAreaElement> {
    private readonly assetService = new AssetService();

    public connect(): void {
        this.element.addEventListener('paste', this.commentPasteListener.bind(this));
    }

    private commentPasteListener(event: ClipboardEvent) {
        const target = <HTMLTextAreaElement>event.target;
        if (!event.clipboardData || !event.clipboardData.items || event.clipboardData.items.length !== 1) {
            return;
        }

        const item         = <DataTransferItem>event.clipboardData.items[0];
        const allowedMimes = ['image/png', 'image/gif', 'image/jpg', 'image/jpeg']
        if (item.kind !== 'file' || allowedMimes.includes(item.type) === false) {
            return;
        }

        const mimeType = item.type;
        const blob     = Assert.notNull(item.getAsFile());
        if (blob.size > 2097152) {
            alert('Pasted file size exceeds allowed file size of 2MB');
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const reader  = new FileReader();
        reader.onload = event => {
            // get data base64 encoded string, and grab just the data string
            const base64data = (<string>event.target!.result).replace(/^[^,]+,/, '')

            this.assetService.uploadImage(mimeType, base64data)
                .then(url => {
                    InputElement.insertAtCursor(target, `![file](${url})\n`);
                    target.dispatchEvent(new Event('input'));
                })
                .catch(Function.empty);
        };
        reader.readAsDataURL(blob);
    }
}
