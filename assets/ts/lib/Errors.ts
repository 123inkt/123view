export default class Errors {
    public static catch(this: void, error: unknown): void {
        // handle Axios error response
        const axiosError = error as {response?: {data?: {error?: string}}};
        if (axiosError.response?.data?.error !== undefined) {
            alert(axiosError.response.data.error);
        }
    }
}
