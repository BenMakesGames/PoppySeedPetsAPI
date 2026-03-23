import {Component, Input, OnChanges, OnDestroy, SimpleChanges} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {PetFriendSerializationGroup} from "../../../../model/my-pet/pet-friend.serialization-group";
import {PetPublicProfileSerializationGroup} from "../../../../model/public-profile/pet-public-profile.serialization-group";
import {Router} from "@angular/router";
import {Subscription} from "rxjs";

@Component({
    selector: 'app-pet-relationships',
    templateUrl: './pet-relationships.component.html',
    styleUrls: ['./pet-relationships.component.scss'],
    standalone: false
})
export class PetRelationshipsComponent implements OnChanges, OnDestroy {

  @Input() pet: PetPublicProfileSerializationGroup;

  relationships: FilterResultsSerializationGroup<PetFriendSerializationGroup>;

  relationshipsAjax: Subscription;

  loading = false;

  constructor(private api: ApiService, private router: Router) { }

  ngOnChanges(changes: SimpleChanges)
  {
    if(changes.pet)
    {
      this.loadPage(0);
    }
  }

  ngOnDestroy(): void {
    if(this.relationshipsAjax)
      this.relationshipsAjax.unsubscribe();
  }

  doViewFriend(friend: PetFriendSerializationGroup)
  {
    this.router.navigateByUrl('/poppyopedia/pet/' + friend.relationship.id);
  }

  loadPage(page: number)
  {
    if(this.loading) return;

    this.loading = true;

    this.relationshipsAjax = this.api.get<FilterResultsSerializationGroup<PetFriendSerializationGroup>>('/pet/' + this.pet.id + '/relationships', { page: page }).subscribe({
      next: r => {
        this.relationships = r.data;
      },
      complete: () => {
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    });
  }

}
