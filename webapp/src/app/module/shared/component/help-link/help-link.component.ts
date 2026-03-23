import { Component, computed, input } from '@angular/core';
import { SvgIconComponent } from "../svg-icon/svg-icon.component";
import { RouterLink } from "@angular/router";

@Component({
    selector: 'app-help-link',
    template: `<a [routerLink]="url()"><app-svg-icon sheet="menu6" icon="meta" title="(learn more about this)"/></a>`,
    imports: [
        SvgIconComponent,
        RouterLink
    ],
    styleUrls: ['./help-link.component.scss']
})
export class HelpLinkComponent {
  link = input.required<string>();
  url = computed(() => `/poppyopedia/help/${this.link()}`);
}
