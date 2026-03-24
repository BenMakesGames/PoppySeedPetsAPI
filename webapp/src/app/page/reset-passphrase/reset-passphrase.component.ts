import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../module/shared/service/api.service";
import { ActivatedRoute, Router } from "@angular/router";
import {UserDataService} from "../../service/user-data.service";
import {Subscription} from "rxjs";
import {MyAccountSerializationGroup} from "../../model/my-account/my-account.serialization-group";

@Component({
    selector: 'app-reset-password',
    templateUrl: './reset-passphrase.component.html',
    styleUrls: ['./reset-passphrase.component.scss'],
    standalone: false
})
export class ResetPassphraseComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Reset Passphrase', song: 'the-ocean' };

  loading = true;
  success = false;
  resetCode;

  passphrase = '';

  private userSubscription: Subscription;
  public user: MyAccountSerializationGroup;

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute, private userData: UserDataService,
    private router: Router
  ) {

  }

  ngOnInit() {
    this.activatedRoute.paramMap.subscribe(
      params => {
        this.resetCode = params.get('code');
        this.loading = false;
      }
    );

    this.userSubscription = this.userData.user.subscribe(u => {
      this.user = u;
    })
  }

  ngOnDestroy() {
    this.userSubscription.unsubscribe();
  }

  doReset()
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/account/requestPassphraseReset/' + this.resetCode, { passphrase: this.passphrase }).subscribe({
      next: () => {
        this.loading = false;
        this.success = true;
      },
      error: () => {
        this.loading = false;
      }
    })
  }

  doLoggedIn()
  {
    this.router.navigateByUrl('/home');
  }

}
