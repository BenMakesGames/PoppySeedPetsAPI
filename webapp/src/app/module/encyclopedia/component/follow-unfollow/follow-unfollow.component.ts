import {Component, Input, OnDestroy, OnInit} from '@angular/core';
import {UserPublicProfileSerializationGroup} from "../../../../model/public-profile/user-public-profile.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {UserDataService} from "../../../../service/user-data.service";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {Subscription} from "rxjs";

@Component({
    selector: 'app-follow-unfollow',
    templateUrl: './follow-unfollow.component.html',
    styleUrls: ['./follow-unfollow.component.scss'],
    standalone: false
})
export class FollowUnfollowComponent implements OnInit, OnDestroy {

  updatingFollow = false;
  user: MyAccountSerializationGroup;
  userSubscription: Subscription;

  @Input() profile: UserPublicProfileSerializationGroup;

  constructor(private api: ApiService, private userDataService: UserDataService) { }

  ngOnInit() {
    this.userSubscription = this.userDataService.user.subscribe(u => {
      this.user = u;
    });
  }

  ngOnDestroy(): void {
    this.userSubscription.unsubscribe();
  }

  doFollow()
  {
    if(this.updatingFollow) return;

    this.updatingFollow = true;
    this.api.post('/following', { following: this.profile.id }).subscribe({
      next: () => {
        this.profile.following = { note: null };
        this.updatingFollow = false;
      },
      error: () => {
        this.updatingFollow = false;
      }
    })
  }

  doStopFollowing()
  {
    if(this.updatingFollow) return;

    this.updatingFollow = true;
    this.api.del('/following/' + this.profile.id).subscribe({
      next: () => {
        delete this.profile.following;
        this.updatingFollow = false;
      },
      error: () => {
        this.updatingFollow = false;
      }
    });
  }

  doUpdateNote()
  {
    if(this.updatingFollow) return;

    this.updatingFollow = true;

    this.api.post('/following/' + this.profile.id, { note: this.profile.following.note }).subscribe({
      next: () => {
        this.updatingFollow = false;
      },
      error: () => {
        this.updatingFollow = false;
      }
    })
  }

}
