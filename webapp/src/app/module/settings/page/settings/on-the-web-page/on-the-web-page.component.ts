import { Component } from '@angular/core';

@Component({
    templateUrl: './on-the-web-page.component.html',
    styleUrls: ['./on-the-web-page.component.scss'],
    standalone: false
})
export class OnTheWebPageComponent {
  pageMeta = { title: 'Settings - My Profile' };
}
