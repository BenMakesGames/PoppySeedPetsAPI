import { Component } from '@angular/core';

@Component({
    selector: 'app-login-choices',
    templateUrl: './login-choices.component.html',
    styleUrls: ['./login-choices.component.scss'],
    standalone: false
})
export class LoginChoicesComponent {

  show = 'logIn';

  constructor() { }
}
