import {Controller} from '@hotwired/stimulus';
import BrowserNotification from '../lib/BrowserNotification';
import Function from '../lib/Function';

export default class extends Controller {
    public static targets         = ['button'];
    private readonly notification = new BrowserNotification();
    private readonly declare buttonTarget: HTMLButtonElement;

    public connect(): void {
        this.update();
    }

    public enable(): void {
        this.notification.requestAccess()
            .then(() => this.update())
            .catch(Function.empty);
    }

    private update(): void {
        const enabled = this.notification.isEnabled();
        this.element.querySelectorAll<HTMLInputElement>('[data-role="notification-event"]').forEach(el => el.disabled = enabled === false);
        this.buttonTarget.disabled = enabled;
    }
}
