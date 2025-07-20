import CodeReview from '@model/entities/CodeReview';
import Repository from '@model/entities/Repository';
import PaginatorViewModel from '@model/viewmodels/PaginatorViewModel';

export default interface ProjectReviewsViewModel {
    repository: Repository;
    reviews: CodeReview[];
    paginator: PaginatorViewModel;
}
