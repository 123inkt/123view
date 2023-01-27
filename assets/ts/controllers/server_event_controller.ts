import {Controller} from '@hotwired/stimulus';
import DataSet from '../lib/DataSet';

export default class extends Controller<HTMLElement> {
    public connect(): void {
        const publishUrl      = DataSet.string(this.element, 'url');
        const eventSource     = new EventSource(publishUrl, {withCredentials: true});
        eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);
            document.dispatchEvent(new CustomEvent(data.topic, {detail: data}));
        };

        window.addEventListener('beforeunload', () => eventSource.close());
    }
}
