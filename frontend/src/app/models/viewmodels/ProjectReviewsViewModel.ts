import CodeReview from '@model/entities/CodeReview';
import Repository from '@model/entities/Repository';
import PaginatorViewModel from '@model/viewmodels/PaginatorViewModel';

export default interface ProjectReviewsViewModel {
    repository: Repository;
    reviews: CodeReview[];
    authors: Record<number, string[]>; // key: review id, value: author names
    reviewers: Record<number, string[]>; // key: review id, value: reviewer names
    reviewStates: Record<number, 'open' | 'in-review' | 'accepted' | 'rejected' | 'closed'>; // key: review id
    paginator: PaginatorViewModel;
}
