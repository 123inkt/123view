import Function from './Function';

export default class BrowserNotification {
    public isEnabled(): boolean {
        return 'Notification' in window && window.Notification.permission === 'granted';
    }

    public publish(title: string, message: string): void {
        // unsupported or blocked
        if ('Notification' in window === false || window.Notification.permission === 'denied') {
            return;
        }

        // granted
        if (window.Notification.permission === 'granted') {
            this.show(title, message);
            return;
        }

        // request permissions
        window.Notification.requestPermission()
            .then(permission => {
                if (permission === 'granted') {
                    this.show(title, message);
                }
            })
            .catch(Function.empty);
    }

    private show(title: string, message: string): void {
        // strip html from the message
        const el     = document.createElement('div');
        el.innerHTML = message.replace(/(<([^>]+)>)/gi, '');
        message      = el.innerText;

        // eslint-disable-next-line no-new
        new Notification(title, {body: message});
    }
}
