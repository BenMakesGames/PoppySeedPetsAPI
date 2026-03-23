import { Component, OnInit } from '@angular/core';

@Component({
    templateUrl: './following.component.html',
    styleUrls: ['./following.component.scss'],
    standalone: false
})
export class FollowingComponent implements OnInit {
  pageMeta = { title: 'Settings - Following' };

  constructor() { }

  ngOnInit() {
  }

}
