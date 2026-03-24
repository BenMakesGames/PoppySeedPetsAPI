import { Component } from '@angular/core';
import {ApiService} from "../../module/shared/service/api.service";

@Component({
    selector: 'app-request-passphrase-reset',
    templateUrl: './request-passphrase-reset.component.html',
    styleUrls: ['./request-passphrase-reset.component.scss'],
    standalone: false
})
export class RequestPassphraseResetComponent {

  loading = false;
  done = false;
  email = '';

  constructor(private api: ApiService) { }

  doRequestReset()
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/account/requestPassphraseReset', { email: this.email }).subscribe({
      next: () => {
        this.done = true;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    })
  }

}
