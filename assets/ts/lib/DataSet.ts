export default class DataSet {
    public static string(element: HTMLElement, key: string, defaultValue: string | null = null): string {
        const value = DataSet.stringOrNull(element, key, defaultValue);
        if (value === null) {
            throw new Error('Missing value for key: ' + key);
        }

        return value;
    }

    public static stringOrNull(element: HTMLElement, key: string, defaultValue: string | null = null): string | null {
        const value = element.dataset[key];
        if (value === undefined || value === null) {
            return defaultValue;
        }

        return value;
    }

    public static int(element: HTMLElement, key: string, defaultValue: number | null = null): number {
        const value = DataSet.intOrNull(element, key, defaultValue);
        if (value === null) {
            throw new Error('Missing value for key: ' + key);
        }

        return value;
    }

    public static intOrNull(element: HTMLElement, key: string, defaultValue: number | null = null): number | null {
        const value = element.dataset[key];
        if (value === undefined || value === null) {
            return defaultValue;
        }

        const intValue = parseInt(value, 10);
        if (isNaN(intValue)) {
            return defaultValue;
        }

        return intValue;
    }
}
