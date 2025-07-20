export function filter<T extends Record<number| string, unknown>>(data: T): T {
    // delete all undefined and null values
    return Object.fromEntries(Object.entries(data).filter(([, value]) => value !== undefined && value !== null)) as T;
}
