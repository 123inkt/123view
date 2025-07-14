import {HttpContextToken} from '@angular/common/http';

export default class HttpClientContext {
    public static readonly PublicUrl = new HttpContextToken<boolean>(() => false);
}
