import {Component, EventEmitter, Input, OnDestroy, OnInit, Output} from '@angular/core';
import {PetFriendSerializationGroup} from "../../../../model/my-pet/pet-friend.serialization-group";
import {ApiService} from "../../service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import { Router, RouterLink } from "@angular/router";
import {SpiritCompanionSerializationGroup} from "../../../../model/my-pet/spirit-companion.serialization-group";
import {PetGroupSerializationGroup} from "../../../../model/my-pet/pet-group.serialization-group";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {UserDataService} from "../../../../service/user-data.service";
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {PetGuildSerializationGroup} from "../../../../model/guild/pet-guild.serialization-group";
import {Subscription} from "rxjs";
import { PetFriendComponent } from "../pet-friend/pet-friend.component";
import { HasUnlockedFeaturePipe } from "../../pipe/has-unlocked-feature.pipe";
import { ProgressBarComponent } from "../progress-bar/progress-bar.component";
import { CommonModule } from "@angular/common";
import { LoadingThrobberComponent } from "../loading-throbber/loading-throbber.component";
import { DateOnlyComponent } from "../date-only/date-only.component";
import { PetGroupProductLabelPipe } from "../../pipe/pet-group-product-label.pipe";
import { PetGroupLabelPipe } from "../../pipe/pet-group-label.pipe";
import { HelpLinkComponent } from "../help-link/help-link.component";

@Component({
    selector: 'app-pet-friends',
    templateUrl: './pet-friends.component.html',
    imports: [
        PetFriendComponent,
        HasUnlockedFeaturePipe,
        ProgressBarComponent,
        CommonModule,
        LoadingThrobberComponent,
        DateOnlyComponent,
        RouterLink,
        PetGroupProductLabelPipe,
        PetGroupLabelPipe,
        HelpLinkComponent
    ],
    styleUrls: ['./pet-friends.component.scss']
})
export class PetFriendsComponent implements OnInit, OnDestroy {

  @Output() linkClick = new EventEmitter<void>();
  @Input() pet: MyPetSerializationGroup;

  readonly GROUP_TYPE_IMAGES = [
    '', // 0
    'band',
    'astronomy',
    'gaming',
    'sportsball'
  ];

  relationships: RelationshipsModel;
  loadingFriends = true;
  user: MyAccountSerializationGroup;
  showSecondColumn = false;
  petFriendsAjax: Subscription;

  constructor(private api: ApiService, private router: Router, private userDataService: UserDataService) {
    this.user = this.userDataService.user.getValue();
  }

  ngOnInit() {
    // ... start loading them!
    this.petFriendsAjax = this.api.get<RelationshipsModel>('/pet/' + this.pet.id + '/friends').subscribe({
      next: (r: ApiResponseModel<RelationshipsModel>) => {
        this.relationships = r.data;
        this.showSecondColumn = (this.relationships.groups && this.relationships.groups.length > 0) || !!this.relationships.spiritCompanion || !!this.relationships.guild;
        this.loadingFriends = false;
      }
    });
  }

  ngOnDestroy(): void {
    this.petFriendsAjax.unsubscribe();
  }

  doClose()
  {
    this.linkClick.emit();
  }

  doViewGroup(group)
  {
    this.linkClick.emit();
    this.router.navigate([ '/poppyopedia/group/' + group.id ]);
  }

  doViewFriend(friend: PetFriendSerializationGroup)
  {
    this.linkClick.emit();
    this.router.navigate([ '/poppyopedia/pet/' + friend.relationship.id ]);
  }
}

interface RelationshipsModel
{
  spiritCompanion: SpiritCompanionSerializationGroup;
  groups: PetGroupSerializationGroup[];
  friends: PetFriendSerializationGroup[];
  relationshipCount: number;
  guild: PetGuildSerializationGroup|null;
}
