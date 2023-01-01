import {Controller} from '@hotwired/stimulus';
import DataSet from '../lib/DataSet';

export default class extends Controller<HTMLElement> {
    public connect() {
        const publishUrl      = DataSet.string(this.element, 'url');
        const eventSource     = new EventSource(publishUrl, {withCredentials: true});
        eventSource.onmessage = (event) => document.dispatchEvent(new CustomEvent('notification', {detail: JSON.parse(event.data)}))
        window.addEventListener('beforeunload', () => eventSource.close());
    }
}
