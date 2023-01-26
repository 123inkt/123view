export default class BrowserNotification {
    public isEnabled(): boolean {
        return 'Notification' in window && window.Notification.permission === 'granted';
    }

    public publish(title: string, message: string): void {
        if (this.isEnabled() === false) {
            return;
        }

        // strip html from the message
        const el     = document.createElement('div');
        el.innerHTML = message.replace(/(<([^>]+)>)/gi, '');
        message      = el.innerText;

        // eslint-disable-next-line no-new
        new Notification(title, {body: message});
    }

    public requestAccess(): Promise<NotificationPermission> {
        return window.Notification.requestPermission();
    }
}
