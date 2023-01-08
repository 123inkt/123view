export default class Errors {
    public static catch(error: any): void {
        // handle Axios error response
        if (error.response?.data?.error !== undefined) {
            alert(error.response.data.error);
        }
    }
}
