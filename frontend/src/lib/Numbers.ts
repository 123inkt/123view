export function toNumber(value: string | null): number {
    if (typeof value !== 'string') {
        throw new Error('Unable to parse number for value of type ' + typeof value);
    }

    const result = window.parseInt(value);
    if (isNaN(result)) {
        throw new Error('Invalid number: ' + value);
    }
    return result;
}

export function range(from: number, to: number) {
    return [...Array(to - from + 1).keys()].map(i => i + from);
}
