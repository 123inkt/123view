import {Pipe, PipeTransform} from '@angular/core';
import {contains} from '@lib/Strings';

@Pipe({name: 'contains'})
export class ContainsPipe implements PipeTransform {
  public transform(value: string, search: string): boolean {
    return contains(value, search);
  }
}
