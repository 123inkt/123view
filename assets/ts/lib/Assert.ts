export default class Assert {
    public static notNull<T>(val: T | null): T {
        if (val === null) {
            throw new Error('Value is null!');
        }
        return val;
    }

    public static notUndefined<T>(val: T | undefined): T {
        if (val === undefined) {
            throw new Error('Value is undefined!');
        }
        return val;
    }
}
