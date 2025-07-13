import CodeReview from '@model/entities/CodeReview';
import User from '@model/entities/User';

export default class CodeReviewActivity {
    public declare id: number;
    public declare eventName: string;
    public declare data: {[key: string]: unknown};
    public user?: User;
    public declare review: CodeReview;
    public declare createTimestamp: number;
}
