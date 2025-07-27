import {Paginator} from '@component/paginator/paginator';
import Repository from '@model/entities/Repository';
import Revision from '@model/entities/Revision';

export default class RepositoryRevisionListViewModel {
    public declare repository: Repository;
    public declare revisions: Revision[];
    public declare paginator: Paginator;
}
