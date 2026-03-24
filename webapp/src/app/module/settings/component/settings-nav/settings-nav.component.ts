import {Component, Input} from '@angular/core';
import {Router} from "@angular/router";

@Component({
    selector: 'app-settings-nav',
    templateUrl: './settings-nav.component.html',
    styleUrls: ['./settings-nav.component.scss'],
    standalone: false
})
export class SettingsNavComponent {

  @Input() active: string;

  constructor(private router: Router) { }

  doNav(path: string) {
    this.router.navigate([ path ]);
  }

}
