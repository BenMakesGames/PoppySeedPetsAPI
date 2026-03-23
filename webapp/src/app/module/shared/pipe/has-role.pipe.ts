import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'hasRole'
})
export class HasRolePipe implements PipeTransform {

  transform(value: null|{ roles: string[] }, role: string): any {
    if(value === null)
      return false;
    else
      return value.roles.indexOf('ROLE_' + role.toUpperCase()) !== -1;
  }

}
