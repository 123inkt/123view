type Subscriptions = Record<string, Array<{reviewId: number; userId?: number; callback: (event: Event) => void}>>;

export default class ReviewNotificationService {
    private subscriptions: Subscriptions = {};

    constructor() {
        this.onEvent = this.onEvent.bind(this);
    }

    public subscribe(channel: string, events: string | string[], callback: (event: Event) => void, reviewId: number, userId?: number): void {
        document.addEventListener(channel, this.onEvent);

        if (typeof events === 'string') {
            events = [events];
        }

        for (const eventName of events) {
            if (this.subscriptions[eventName] === undefined) {
                this.subscriptions[eventName] = [];
            }
            this.subscriptions[eventName]?.push({reviewId, userId, callback});
        }
    }

    public unsubscribe(channel: string): void {
        document.removeEventListener(channel, this.onEvent);
    }

    private onEvent(event: Event): void {
        const data      = (event as CustomEvent).detail;
        const eventName = data.eventName;

        const subscriptions = this.subscriptions[eventName];
        if (subscriptions === undefined) {
            return;
        }

        for (const subscription of subscriptions) {
            if (data.reviewId !== subscription.reviewId) {
                continue;
            }

            if (subscription.userId !== undefined && data.userId !== subscription.userId) {
                continue;
            }

            subscription.callback(event);
        }
    }
}
