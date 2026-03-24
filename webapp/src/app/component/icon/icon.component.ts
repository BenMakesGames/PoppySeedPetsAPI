import {Component, Input, OnInit} from '@angular/core';

@Component({
    selector: 'app-icon',
    template: `<img [src]="'assets/images/icons/' + icon + '.svg'" [style.width]="size" [style.height]="size" [alt]="alt">`,
    styleUrls: ['./icon.component.scss'],
    standalone: false
})
export class IconComponent implements OnInit {

  @Input() alt: string = '';
  @Input() size: string = '1rem';
  @Input() icon: string;

  constructor() { }

  ngOnInit() {
  }

}
