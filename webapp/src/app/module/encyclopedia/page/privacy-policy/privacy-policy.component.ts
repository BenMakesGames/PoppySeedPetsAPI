import { Component, OnInit } from '@angular/core';

@Component({
    templateUrl: './privacy-policy.component.html',
    styleUrls: ['./privacy-policy.component.scss'],
    standalone: false
})
export class PrivacyPolicyComponent implements OnInit {
  pageMeta = { title: 'Poppyopedia - Cookies & Privacy Policy' };

  constructor() { }

  ngOnInit() {
  }

}
