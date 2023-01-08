import {Controller} from '@hotwired/stimulus';
import Assert from '../lib/Assert';
import Events from '../lib/Events';
import Function from '../lib/Function';
import InputElement from '../lib/InputElement';
import AssetService from '../service/AssetService';

export default class extends Controller<HTMLTextAreaElement> {
    private readonly assetService = new AssetService();

    public connect(): void {
        this.element.addEventListener('paste', this.commentPasteListener.bind(this));
    }

    private commentPasteListener(event: ClipboardEvent): void {
        const target = event.target as HTMLTextAreaElement;
        if (!event.clipboardData || !event.clipboardData.items || event.clipboardData.items.length !== 1) {
            return;
        }

        const item         = event.clipboardData.items[0] as DataTransferItem;
        const allowedMimes = ['image/png', 'image/gif', 'image/jpg', 'image/jpeg'];
        if (item.kind !== 'file' || allowedMimes.includes(item.type) === false) {
            return;
        }

        const mimeType = item.type;
        const blob     = Assert.notNull(item.getAsFile());
        if (blob.size > 2097152) {
            alert('Pasted file size exceeds allowed file size of 2MB');
            return;
        }

        Events.stop(event);

        const reader  = new FileReader();
        reader.onload = event => {
            // get data base64 encoded string, and grab just the data string
            const base64data = (event.target!.result as string).replace(/^[^,]+,/, '');

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
