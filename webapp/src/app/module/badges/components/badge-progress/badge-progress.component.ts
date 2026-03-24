import { Component, Input } from '@angular/core';

@Component({
    selector: 'app-badge-progress',
    templateUrl: './badge-progress.component.html',
    styleUrls: ['./badge-progress.component.scss'],
    standalone: false
})
export class BadgeProgressComponent {
  @Input() progress: { target: number, current: number };
}
