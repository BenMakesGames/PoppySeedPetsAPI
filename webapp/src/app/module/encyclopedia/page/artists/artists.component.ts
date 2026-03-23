import { Component } from '@angular/core';

@Component({
    templateUrl: './artists.component.html',
    styleUrls: ['./artists.component.scss'],
    standalone: false
})
export class ArtistsComponent {
  pageMeta = { title: 'Poppyopedia - Artists' };

  constructor() { }

}
