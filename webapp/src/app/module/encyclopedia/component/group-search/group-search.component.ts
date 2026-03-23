import {Component, EventEmitter, Input, OnDestroy, OnInit, Output} from '@angular/core';
import {UserDataService} from "../../../../service/user-data.service";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {Subscription} from "rxjs";
import {PetGroupSearchModel} from "../../../../model/search/pet-group-search.model";

@Component({
    selector: 'app-group-search',
    templateUrl: './group-search.component.html',
    styleUrls: ['./group-search.component.scss'],
    standalone: false
})
export class GroupSearchComponent implements OnInit, OnDestroy {

  @Input() search: PetGroupSearchModel = {
    name: '',
    type: '',
    withPetsOwnedBy: null,
  };
  @Output() searchChange = new EventEmitter<PetGroupSearchModel>();

  showMore = false;
  withPetsOwnedByCheckbox = false;

  user: MyAccountSerializationGroup;
  userSubscription: Subscription;

  constructor(private userData: UserDataService) { }

  ngOnInit() {
    this.userSubscription = this.userData.user.subscribe({
      next: u => { this.user = u; }
    });
  }

  ngOnDestroy() {
    this.userSubscription.unsubscribe();
  }

  doShowLess()
  {
    this.showMore = false;
    this.withPetsOwnedByCheckbox = false;

    this.search.type = '';
    this.search.withPetsOwnedBy = null;
  }

  doSetFilter(propertyName: string, value: any)
  {
    this.search[propertyName] = value;
    this.doChange();
  }

  doChange()
  {
    this.searchChange.emit(this.search);
  }

}
