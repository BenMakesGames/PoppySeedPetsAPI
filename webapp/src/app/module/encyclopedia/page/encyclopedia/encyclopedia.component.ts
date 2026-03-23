import { Component, OnInit } from '@angular/core';

@Component({
    templateUrl: './encyclopedia.component.html',
    styleUrls: ['./encyclopedia.component.scss'],
    standalone: false
})
export class EncyclopediaComponent implements OnInit {
  pageMeta = { title: 'Poppyopedia' };

  constructor() { }

  ngOnInit() {
  }

}
