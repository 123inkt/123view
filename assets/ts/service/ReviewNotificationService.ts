interface Subscriptions {
    [key: string]: {reviewId: number; userId?: number; callback: (event: Event) => void}[];
}

export default class ReviewNotificationService {
    private subscriptions: Subscriptions = {};

    constructor() {
        this.onEvent = this.onEvent.bind(this);
    }

    public subscribe(events: string | string[], callback: (event: Event) => void, reviewId: number, userId?: number): void {
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

    public onEvent(event: Event): void {
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
