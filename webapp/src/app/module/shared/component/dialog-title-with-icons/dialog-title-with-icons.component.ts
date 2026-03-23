import { Component, input } from '@angular/core';

@Component({
    selector: 'app-dialog-title-with-icons',
    imports: [],
    templateUrl: './dialog-title-with-icons.component.html',
    styleUrls: ['./dialog-title-with-icons.component.scss']
})
export class DialogTitleWithIconsComponent {
  icon = input.required<string>();
  iconLabel = input.required<string>();
  title = input.required<string>();
}
