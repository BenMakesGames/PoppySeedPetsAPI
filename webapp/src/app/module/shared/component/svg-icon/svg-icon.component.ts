import { Component, Input } from '@angular/core';

@Component({
  standalone: true,
  selector: 'app-svg-icon',
  template: `<svg><use [attr.xlink:href]="'/assets/images/icons/' + sheet + '.svg#' + icon"></use></svg>`,
  styleUrls: ['./svg-icon.component.scss']
})
export class SvgIconComponent {
  @Input() sheet = '';
  @Input() icon = '';
}
