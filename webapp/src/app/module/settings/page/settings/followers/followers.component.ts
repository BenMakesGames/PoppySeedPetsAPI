import { Component, OnInit } from '@angular/core';

@Component({
    templateUrl: './followers.component.html',
    styleUrls: ['./followers.component.scss'],
    standalone: false
})
export class FollowersComponent implements OnInit {
  pageMeta = { title: 'Settings - Followers' };

  constructor() { }

  ngOnInit() {
  }

}
