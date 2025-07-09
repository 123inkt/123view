import Comment from '@model/entities/Comment';

export default class TimelineViewModel {
  public declare comment: Comment | null;
  public declare reply: Comment | null;
  public declare revision: null;
  public declare activities: [];
  public declare message: string;
  public declare url: string;
}
