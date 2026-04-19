/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
