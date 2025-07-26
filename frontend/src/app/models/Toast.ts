export default class Toast {
    constructor(
        public readonly message: string,
        public readonly type: 'success' | 'error' | 'info' = 'info',
        private readonly onClose?: (toast: Toast) => void
    ) {
    }

    public close(): void {
        this.onClose?.(this);
    }
}
