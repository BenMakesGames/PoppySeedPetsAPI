import { Component } from '@angular/core';

@Component({
    selector: 'app-theme-grid',
    template: '<div class="themes"><ng-content></ng-content></div>',
    styleUrls: ['./theme-grid.component.scss'],
    standalone: false
})
export class ThemeGridComponent {
}
