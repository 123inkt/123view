import axios, {AxiosRequestConfig, AxiosResponse} from 'axios';

export default class HttpClient {
    private abortController: AbortController | null = null;

    public get<T = any, R = AxiosResponse<T>, D = any>(url: string, config: AxiosRequestConfig<D> = {}): Promise<R> {
        return this.wrap(config, () => axios.get(url, config));
    }

    public post<T = any, R = AxiosResponse<T>, D = any>(url: string, data?: D, config: AxiosRequestConfig<D> = {}): Promise<R> {
        return this.wrap(config, () => axios.post(url, data, config));
    }

    public delete<T = any, R = AxiosResponse<T>, D = any>(url: string, config: AxiosRequestConfig<D> = {}): Promise<R> {
        return this.wrap(config, () => axios.delete(url, config));
    }

    public form<T = any, R = AxiosResponse<T>>(form: HTMLFormElement): Promise<R> {
        if (form.method.toLowerCase() !== 'post') {
            throw new Error('Only POST forms are supported');
        }
        return this.post<T, R>(form.action, new FormData(form), {headers: {'Content-Type': form.encoding}});
    }

    private wrap<T = any, R = AxiosResponse<T>, D = any>(config: AxiosRequestConfig<D>, callback: () => Promise<R>): Promise<R> {
        if (this.abortController !== null) {
            this.abortController.abort();
        }

        this.abortController = new AbortController();
        config.signal        = this.abortController.signal;

        return callback();
    }
}
