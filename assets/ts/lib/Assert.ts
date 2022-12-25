export default class Assert {
    public static notNull<T>(val: T | null): T {
        if (val === null) {
            throw new Error('Value is null!');
        }
        return val;
    }
}
