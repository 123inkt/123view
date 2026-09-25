export default class BrowserNotification {
    public isEnabled(): boolean {
        return 'Notification' in window && window.Notification.permission === 'granted';
    }

    public publish(title: string, message: string, tag: string, url?: string): void {
        if (this.isEnabled() === false) {
            return;
        }

        // strip html from the message
        const el          = document.createElement('div');
        el.innerHTML      = message.replace(/(<([^>]+)>)/gi, '');
        const textMessage = el.innerText;

        const notification = new Notification(title, {tag, body: textMessage});
        if (url !== undefined) {
            notification.addEventListener('click', () => window.location.href = url);
        }
    }

    public requestAccess(): Promise<NotificationPermission> {
        return window.Notification.requestPermission();
    }
}
