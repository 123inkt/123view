import {Controller} from '@hotwired/stimulus';
import type MercureEvent from '../entity/MercureEvent';
import BrowserNotification from '../lib/BrowserNotification';

export default class extends Controller {
    public static values = {userId: Number};

    private readonly notification = new BrowserNotification();
    private readonly declare userIdValue: number;

    public connect(): void {
        document.addEventListener(`/user/${String(this.userIdValue)}`, this.handleNotification.bind(this));
    }

    private handleNotification(event: Event): void {
        const data = (event as CustomEvent<MercureEvent>).detail;
        this.notification.publish(data.title, data.message, `tag-${String(data.eventId)}`, data.url);
    }
}
