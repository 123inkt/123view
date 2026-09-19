import axios, {type AxiosRequestConfig, type AxiosResponse} from 'axios';

export default class HttpClient {
    private abortController: AbortController | null = null;

    public get<T = unknown, D = unknown>(url: string, config: AxiosRequestConfig<D> = {}): Promise<AxiosResponse<T, D>> {
        return this.wrap(config, () => axios.get<T, AxiosResponse<T, D>, D>(url, config));
    }

    public post<T = unknown, D = unknown>(url: string, data?: D, config: AxiosRequestConfig<D> = {}): Promise<AxiosResponse<T, D>> {
        return this.wrap(config, () => axios.post<T, AxiosResponse<T, D>, D>(url, data, config));
    }

    public delete<T = unknown, D = unknown>(url: string, config: AxiosRequestConfig<D> = {}): Promise<AxiosResponse<T, D>> {
        return this.wrap(config, () => axios.delete<T, AxiosResponse<T, D>, D>(url, config));
    }

    public form<T = unknown>(form: HTMLFormElement, params?: unknown): Promise<AxiosResponse<T, FormData>> {
        if (form.method.toLowerCase() !== 'post') {
            throw new Error('Only POST forms are supported');
        }
        return this.post<T, FormData>(form.action, new FormData(form), {headers: {'Content-Type': form.encoding}, params});
    }

    private wrap<T = unknown, R = AxiosResponse<T>, D = unknown>(config: AxiosRequestConfig<D>, callback: () => Promise<R>): Promise<R> {
        if (this.abortController !== null) {
            this.abortController.abort();
        }

        this.abortController = new AbortController();
        config.signal        = this.abortController.signal;

        return callback();
    }
}
