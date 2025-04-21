import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class GraphChangesService {

  changes = new Subject<void>();

  constructor() { }
}
