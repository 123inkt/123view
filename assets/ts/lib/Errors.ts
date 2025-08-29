export default class Errors {
    public static catch(this: void, error: {response?: {data?: {error?: string}}}): void {
        // handle Axios error response
        if (error.response?.data?.error !== undefined) {
            alert(error.response.data.error);
        }
    }
}
