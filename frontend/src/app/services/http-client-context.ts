import {HttpContextToken} from '@angular/common/http';

export default class HttpClientContext {
    public static readonly PublicUrl         = new HttpContextToken<boolean>(() => false);
    public static readonly BackendApi        = new HttpContextToken<boolean>(() => false);
}
