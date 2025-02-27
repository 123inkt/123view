import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    public onClick(event: Event): void {
        event.preventDefault();

        const iframe = document.createElement('iframe');
        document.body.appendChild(iframe);
        iframe.onload        = () => iframe.remove();
        iframe.onerror       = () => iframe.remove();
        iframe.style.display = 'block';
        iframe.style.width   = '0px';
        iframe.style.height  = '0px';
        iframe.src           = (this.element as HTMLAnchorElement).href;
    }
}
